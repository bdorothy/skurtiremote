<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by keywords
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_filter_keywords extends Mtt_search_filter {

	/**
	 * Priority for this filter
	 */
	protected $priority = 10;

	/**
	 * Current collections
	 */
	private $_collections = array();

	/**
	 * Keywords
	 */
	private $_terms = array();
	private $_keywords = array();
	private $_fulltext;

	/**
	 * Search results for this filter
	 */
	private $_results;

	/**
	 * Excerpt mapping
	 */
	private $_excerpts = array();

	// --------------------------------------------------------------------

	/**
	 * Als for keywords="" parameter, along with its associated params
	 *
	 * @access     private
	 * @return     void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Set internal collections array based on param
		// --------------------------------------

		$this->_collections = ($cols = $this->params->get('collection'))
			? ee()->mtt_search_collection_model->get_by_param($cols)
			: array();

		// --------------------------------------
		// Check validity of search mode and loose ends
		// --------------------------------------

		$this->_search_mode();
		$this->_loose_ends();

		// --------------------------------------
		// Check keywords and set the search terms
		// --------------------------------------

		$this->_set_terms();

		// --------------------------------------
		// Only perform actual search if keywords are given
		// --------------------------------------

		if (empty($this->_terms))
		{
			$this->_log('No keyword search');
			$this->_prep_params();
			return $entry_ids;
		}

		// --------------------------------------
		// Reset results
		// --------------------------------------

		$this->_results = array();

		// --------------------------------------
		// Begin composing query
		// --------------------------------------

		ee()->db->select(($this->_fulltext
			? "entry_id, collection_id, MATCH(index_text) AGAINST('{$this->_query()}') AS score"
			: 'entry_id, collection_id, index_text'), FALSE)
			->from(ee()->mtt_search_index_model->table());

		// --------------------------------------
		// Filters used by both searches
		// --------------------------------------

		// Limit query by collection
		if ($this->_collections)
		{
			ee()->db->where_in('collection_id', mtt_flatten_results($this->_collections, 'collection_id'));
		}

		// Limit query by site
		if ($site_ids = $this->params->site_ids())
		{
			ee()->db->where_in('site_id', array_values($site_ids));
		}

		// If entry ids were given, limit to those
		if ($entry_ids)
		{
			ee()->db->where_in('entry_id', $entry_ids);
		}

		// Add where clause
		ee()->db->where(($this->_fulltext ? $this->_fulltext_keywords() : $this->_fallback_keywords()), NULL, FALSE);

		if ($this->_fulltext)
		{
			// Actual fulltext search
			ee()->db->order_by('score', 'desc');

			// Limit by min_score
			if ($this->params->get('min_score'))
			{
				list($score, $include) = $this->_min_score();
				$oper = $include ? '>=' : '>';
				ee()->db->having("score {$oper}", $score);
			}
		}

		// --------------------------------------
		// Extra search stuff
		// --------------------------------------

		if ($add_to_query = $this->params->get_prefixed('keywords-query:', TRUE))
		{
			foreach ($add_to_query AS $field => $val)
			{
				if (ee()->db->field_exists($field, ee()->mtt_search_index_model->table()))
				{
					list($items, $in) = mtt_explode_param($val);
					ee()->db->{($in ? 'where_in' : 'where_not_in')}($field, $val);
				}
				else
				{
					$this->_log("Field {$field} does not exist in ".ee()->mtt_search_index_model->table());
				}
			}
		}

		// --------------------------------------
		// Perform the search
		// --------------------------------------

		$this->_log('Starting search '.($this->_fulltext ? '(fulltext)' : '(fallback)'));
		$query = ee()->db->get();

		// --------------------------------------
		// If the search had no results, return no results bit
		// --------------------------------------

		if ($query->num_rows == 0)
		{
			$this->_log('Searched but found nothing. Returning no results.');
			return array();
		}

		// --------------------------------------
		// If we do have results, continue
		// --------------------------------------

		$this->_results = $this->_fulltext
			? mtt_associate_results($query->result_array(), 'entry_id')
			: $this->_get_fallback_results($query);

		// Bail out if no entry falls above the min_score threshold
		if (empty($this->_results))
		{
			$this->_log('No valid results after scoring');
			return array();
		}

		// --------------------------------------
		// Modify scores for each collection
		// --------------------------------------

		if ($modifiers = array_unique(mtt_flatten_results($this->_collections, 'modifier')))
		{
			if ( ! (count($modifiers) == 1 && $modifiers[0] == 1.0))
			{
				$this->_log('Applying collection modifier to search results');

				foreach ($this->_results AS &$row)
				{
					if ($mod = (float) $this->_collections[$row['collection_id']]['modifier'])
					{
						$row['score'] = $row['score'] * $mod;
					}
				}
			}
		}

		// -------------------------------------
		// 'mtt_search_modify_score' hook.
		//  - Modify scoring for keyword searches
		// -------------------------------------

		if (ee()->extensions->active_hook('mtt_search_modify_score') === TRUE)
		{
			$this->_results = ee()->extensions->call('mtt_search_modify_score', $this->_results);

			if (empty($this->_results) || ee()->extensions->end_script === TRUE)
			{
				return array();
			}
		}

		// --------------------------------------
		// Sort by score
		// --------------------------------------

		uasort($this->_results, 'mtt_by_score');

		// --------------------------------------
		// Add results to cache, so extension can look this up
		// --------------------------------------

		return array_keys($this->_results);
	}

	/**
	 * Fixed order?
	 */
	public function fixed_order()
	{
		$fixed = FALSE;

		// --------------------------------------
		// Is there a custom sort order?
		// If so, set the entry_id param instead of the fixed_order
		// --------------------------------------

		if ($this->_query())
		{
			$orderby = $this->params->get('orderby', 'mtt_search_score');

			if (substr($orderby, 0, 16) == 'mtt_search_score')
			{
				$fixed = TRUE;

				// Unset so they don't get re-set
				$this->params->set('orderby', '');
				$this->params->set('sort', '');
			}

		}

		return $fixed;
	}

	/**
	 * Prep parameters for keyword-less search
	 */
	private function _prep_params()
	{
		// --------------------------------------
		// No keyword search means no excerpt from collection,
		// so we need to get the excerpt field from the
		// channel preferences. We'll do that here and store
		// the ids in cache for later use
		// --------------------------------------

		// Get channel excerpt data from DB
		ee()->db->select('channel_name, channel_id, search_excerpt')->from('channels');

		// Filter by given collections
		if ($this->_collections)
		{
			ee()->db->where_in('channel_id', mtt_flatten_results($this->_collections, 'channel_id'));
		}

		// Get the data
		$channels = ee()->db->get()->result_array();
		$this->_excerpts = mtt_flatten_results($channels, 'search_excerpt', 'channel_id');

		// --------------------------------------
		// If collections were given, we need to filter
		// the channel entries by channel, so we'll set
		// the channel parameter based on collections
		// --------------------------------------

		if ($this->_collections && $channels)
		{
			$channels = array_unique(mtt_flatten_results($channels, 'channel_name'));
			$this->params->set('channel', mtt_implode_param($channels));
		}

		// Remove mtt_search_score from the orderby parameter and adjust sort param accordingly
		$orderby = $this->params->get('orderby');

		if ($orderby && substr($orderby, 0, 16) == 'mtt_search_score')
		{
			$this->params->set('orderby', preg_replace('#^mtt_search_score\|?#', '', $orderby));

			if ($sort = $this->params->get('sort'))
			{
				$this->params->set('sort', preg_replace('#^(asc|desc)\|?#i', '', $sort));
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Populate internal Terms array
	 */
	private function _set_terms()
	{
		// --------------------------------------
		// Local cache of stop words
		// --------------------------------------

		static $stop_words;

		// --------------------------------------
		// Initiate fulltext mode to TRUE
		// --------------------------------------

		$this->_fulltext = TRUE;

		// --------------------------------------
		// If mode is auto and loose ends is left/both, no fulltext
		// --------------------------------------

		if (($mode = $this->params->get('search_mode')) != 'auto' &&
			in_array($this->params->get('loose_ends'), array('left', 'both')))
		{
			$this->_fulltext = FALSE;
		}

		// --------------------------------------
		// Reset terms
		// --------------------------------------

		$this->_terms = $this->_keywords = array();

		// --------------------------------------
		// Get raw keywords from parameters
		// --------------------------------------

		$words = (string) $this->params->get('qt');

		// --------------------------------------
		// Replace pipes with spaces as if it were separate words
		// --------------------------------------

		$words = str_replace('|', ' ', $words);

		// --------------------------------------
		// Exact match?
		// --------------------------------------

		if ($mode == 'exact')
		{
			$words = '"' . str_replace('"', '', $words) . '"';
		}

		// --------------------------------------
		// Use tokens to get to each term,
		// including quoted ones
		// --------------------------------------

		for ($word = strtok($words, ' '); $word !== FALSE; $word = strtok(' '))
		{
			// Create new Term Object
			$term = new Mtt_search_term();

			// Is term part of an OR group
			$group = FALSE;

			if ($mode == 'auto')
			{
				// Check if search term is part of an OR group
				if ($word == 'OR')
				{
					// Grab the next one or bail
					if (($word = strtok(' ')) === FALSE) break;

					if ($total = count($this->_terms))
					{
						// Set the previous token to a term_group, if it's not already
						$prev = $this->_terms[$total - 1];

						if ( ! ($prev instanceof Mtt_search_term_group))
						{
							$prev = new Mtt_search_term_group(array($prev));
							$this->_terms[$total - 1] = $prev;
						}

						$group = TRUE;
					}
				}

				// Negation?
				if ($term->exclude = (substr($word, 0, 1) == '-'))
				{
					$word = ltrim($word, '-');
				}

				// Loose ends on the left
				if ($term->loose_left = (substr($word, 0, 1) == '*'))
				{
					$word = ltrim($word, '*');
					$this->_fulltext = FALSE;
				}

				// Loose ends on the right
				if ($term->loose_right = (substr($word, -1) == '*'))
				{
					$word = rtrim($word, '*');
				}
			}

			// Check for quoted terms
			if (substr($word, 0, 1) == '"')
			{
				// 1-word quote or not?
				$word = (substr($word, -1) == '"')
				     ? substr($word, 1, -1)
				     : substr($word, 1).' '.strtok('"');

				// Set exact marker to TRUE
				$term->exact = TRUE;
			}

			// Non-exact match, strip ignore words from term
			$ignore = $term->exact
				? array()
				: ee()->mtt_search_settings->get('ignore_words');

			// Get the cleaned search term
			$term->term = mtt_clean_string($word, $ignore);

			// Skip the rest if nothing's left
			if ( ! $term->term) continue;

			// Add term object to previous group or general terms
			$group ? ($prev->terms[] = $term) : ($this->_terms[] = $term);

			// Add whole thing to the keywords array if we're not excluding
			if ( ! $term->exclude)
			{
				$this->_keywords[] = $term->term;
			}

			// Check if keywords are fulltext-worthy
			if ($this->_fulltext)
			{
				foreach (explode(' ', $term->term) AS $word)
				{
					// Get word length
					$length = MB_ENABLED ? mb_strlen($word) : strlen($word);

					if ($length < ee()->mtt_search_settings->get('min_word_length'))
					{
						$this->_fulltext = FALSE;
						continue;
					}

					// Make sure stop words are an array
					if ( ! is_array($stop_words))
					{
						// Clean stopwords and convert into array
						$stop_words = (array) array_unique(preg_split('/\s+/',
							mtt_clean_string(ee()->mtt_search_settings->get('stop_words'))
						));
					}

					if (in_array($word, $stop_words))
					{
						$this->_fulltext = FALSE;
						continue;
					}
				}
			}
		}

		// Final ckeck to see if we're dealing with fulltext or not:
		// Empty keywords is sign that there's only negated keywords,
		// which cannot use fulltext...
		if ($this->_fulltext && empty($this->_keywords))
		{
			$this->_fulltext = FALSE;
		}
	}

	/**
	 * Cleaned up search query given
	 */
	private function _query()
	{
		return implode(' ', $this->_keywords);
	}

	/**
	 * Make sure the loose_ends parameter is backward compatible
	 */
	private function _loose_ends()
	{
		$loose_ends   = $this->params->get('loose_ends');
		$valid_values = array('left', 'right', 'both');

		if ( ! empty($loose_ends) && ! in_array($loose_ends, $valid_values))
		{
			$this->params->set('loose_ends', ($loose_ends == 'no') ? '' : 'right');
		}
	}

	/**
	 * Make sure search_mode is present in parameters
	 */
	private function _search_mode()
	{
		if ( ! in_array($this->params->get('search_mode'), ee()->mtt_search_settings->search_modes))
		{
			$this->params->set('search_mode', ee()->mtt_search_settings->get('default_search_mode'));
		}
	}

	/**
	 * Get min score based on param
	 */
	private function _min_score()
	{
		$min_score = $this->params->get('min_score');
		$include   = FALSE;

		// If param starts with =, it's inclusive
		if (substr($min_score, 0, 1) == '=')
		{
			$min_score = substr($min_score, 1);
			$include   = TRUE;
		}

		return array($min_score, $include);
	}

	/**
	 * Get fallback results and calculate score
	 */
	private function _get_fallback_results($query)
	{
		$this->_log('Calculating relevance score');

		// Calculate scores ourselves
		$results       = array();
		$pattern       = $this->_get_pattern();
		$keyword_count = count($this->_keywords);

		// --------------------------------------
		// Loop thru results, calculate score
		// based on total words / word count
		// --------------------------------------

		foreach ($query->result() AS $row)
		{
			// Calculate score
			$score = 0;

			// But only if we have valid keywords
			if ($keyword_count)
			{
				// Check occurrence of each word in index_text
				// Added score is number of occurrences / total words * 10
				if ($found = preg_match_all($pattern, $row->index_text, $matches))
				{
					// Removes weight
					$text = preg_replace('/^\|(.+?)\|.*$/m', '$1', $row->index_text);

					// Safe word count
					$word_count = count(explode(' ', trim($text)));

					// Add score
					$score = ($found / $word_count) * 100 / $keyword_count;
				}

				// Skip entries that fall be the threshold
				if ($this->params->get('min_score'))
				{
					list($min_score, $include) = $this->_min_score();
					if (($include && $score < $min_score) || (!$include && $score <= $min_score)) continue;
				}
			}

			// Add row to results only if the entry doesn't exist yet
			// or if existing score is er than this one
			if ( ! array_key_exists($row->entry_id, $results) || $results[$row->entry_id]['score'] < $score)
			{
				$results[$row->entry_id] = array(
					'entry_id'      => $row->entry_id,
					'collection_id' => $row->collection_id,
					'score'         => $score
				);
			}
		}

		return $results;
	}

	/**
	 * Compose fulltext keywords based on terms and params
	 *
	 * @access      private
	 * @return      string
	 */
	private function _fulltext_keywords()
	{
		$mode  = $this->params->get('search_mode');
		$loose = $this->params->get('loose_ends');
		$tmpl  = "MATCH(index_text) AGAINST('%s' IN BOOLEAN MODE)";

		if ($mode == 'exact')
		{
			$where = '"'.$this->_query().'"';
		}
		elseif ($mode == 'auto')
		{
			$tmp = array();

			foreach ($this->_terms AS $obj)
			{
				if ($obj instanceof Mtt_search_term_group)
				{
					$group = array();

					foreach ($obj->terms AS $term)
					{
						$group[] = $term->get_fulltext_term(FALSE);
					}

					$tmp[] = '+('.implode(' ', $group).')';
				}
				else
				{
					$tmp[] = $obj->get_fulltext_term();
				}
			}

			$where = implode(' ', $tmp);
		}
		else
		{
			$tmp = array();

			foreach ($this->_keywords AS $word)
			{
				// Exact match for multiple words
				if (strpos($word, ' ') !== FALSE)
				{
					$word = '"'.$word.'"';
				}

				// Add + for all words
				if ($mode == 'all')
				{
					$word = '+'.$word;
				}

				// Account for loose ends on the right
				if ($loose == 'right')
				{
					$word = $word.'*';
				}

				$tmp[] = $word;
			}

			$where = implode(' ', $tmp);
		}

		return sprintf($tmpl, $where);
	}

	/**
	 * Compose fallback where clause terms and params
	 *
	 * @access      private
	 * @return      string
	 */
	private function _fallback_keywords()
	{
		$where = array();
		$tmpl  = '(index_text %s)';

		if ($this->params->get('search_mode') == 'auto')
		{
			foreach ($this->_terms AS $term)
			{
				if ($term instanceof Mtt_search_term_group)
				{
					$group = array();

					foreach ($term->terms AS $t)
					{
						$group[] = sprintf($tmpl, $t->get_fallback_term());
					}

					$where[] = '('.implode(' OR ', $group).')';
				}
				else
				{
					$where[] = sprintf($tmpl, $term->get_fallback_term());
				}
			}

			$where = implode(' AND ', $where);
		}
		else
		{
			foreach ($this->_keywords AS $term)
			{
				// By default, add space around term for exact word matching
				$term = " {$term} ";

				// Remove spaces depending on loose ends
				switch ($this->params->get('loose_ends'))
				{
					case 'left' : $term = ltrim($term); break;
					case 'right': $term = rtrim($term); break;
					case 'both' : $term = trim($term);  break;
				}

				// Add to where clause
				$where[] = " (index_text LIKE '%{$term}%') ";
			}

			// Split by AND or OR
			$andor = ($this->params->get('search_mode') == 'any') ? 'OR' : 'AND';

			// get it together
			$where = implode($andor, $where);
		}

		// Return parenthesised where clause
		return '('.$where.')';
	}

	// --------------------------------------------------------------------

	/**
	 * Modify a row for a search result for this filter
	 */
	public function results($rows)
	{
		// -------------------------------------------
		// Shortcut to prefix
		// -------------------------------------------

		$pfx = ee()->mtt_search_settings->prefix;

		// -------------------------------------------
		// Prep collection info
		// -------------------------------------------

		if ( ! $this->_collections && $this->_results)
		{
			$col_ids = mtt_flatten_results($this->_results, 'collection_id');
			$col_ids = array_unique($col_ids);
			$this->_collections = ee()->mtt_search_collection_model->get_by_id($col_ids);
		}

		// -------------------------------------------
		// Loop through results and do yer thing
		// -------------------------------------------

		foreach ($rows AS &$row)
		{
			// Get score for this entry
			$row[$pfx.'score'] = $this->_get_score($row['entry_id']);

			// Add collection info to row
			foreach ($this->_get_collection_info($row['entry_id']) AS $key => $val)
			{
				$row[$pfx.$key] = $val;
			}
		}

		// -------------------------------------------
		// No excerpt var in tagdata? No need to proceed.
		// -------------------------------------------

		if (strpos(ee()->TMPL->tagdata, $pfx.'excerpt') === FALSE)
		{
			return $rows;
		}

		// -------------------------------------------
		// Load typography lib
		// -------------------------------------------

		ee()->load->library('typography');

		// -------------------------------------------
		// Get all entry ids we're working with
		// -------------------------------------------

		$entry_ids = mtt_flatten_results($rows, 'entry_id');

		// -------------------------------------------
		// Loop through results and add the excerpt
		// -------------------------------------------

		foreach ($rows AS &$row)
		{
			// Get excerpt ID, the field ID to use as excerpt; 0 for title
			$eid = $this->_get_excerpt_id($row);

			// Skip if no valid excerpt ID is found
			if ($eid === FALSE) continue;

			// Get string and format for excerpt
			$str = ($eid == '0' || ! isset($row['field_id_'.$eid]))  ? $row['title'] : $row['field_id_'.$eid];
			$fmt = ($eid == '0' || ! isset($row['field_fmt_'.$eid])) ? 'xhtml'       : $row['field_fmt_'.$eid];

			// -------------------------------------------
			// 'mtt_search_excerpt' hook
			// - change the excerpt for an entry
			// -------------------------------------------

			if (ee()->extensions->active_hook('mtt_search_excerpt') === TRUE)
			{
				$str = ee()->extensions->call('mtt_search_excerpt', $entry_ids, $row, $eid);

				// Check return value
				if (is_array($str) && count($str) == 2)
				{
					// Set excerpt string to first item in array
					list($str, $skip) = $str;

					// If second item in return value, skip native creation of excerpt
					if ($skip === TRUE)
					{
						$row[$pfx.'excerpt'] = $str;
						continue;
					}
				}
			}

			// Overwrite empty excerpt with formatted one
			$row[$pfx.'excerpt'] = $this->_create_excerpt($str, $fmt);

			// Highlight keywords if we have 'em
			if ($this->_keywords)
			{
				$row[$pfx.'excerpt'] = $this->_highlight($row[$pfx.'excerpt']);

				if (ee()->mtt_search_settings->get('title_hilite') == 'y')
				{
					$row['title'] = $this->_highlight($row['title']);
				}
			}
		}

		return $rows;
	}

	/**
	 * Get regex pattern for current search terms
	 */
	private function _get_pattern()
	{
		// Account for non-exact matching
		$delim = ($this->params->get('search_mode') == 'exact') ? ' ' : '|';
		$keywords = implode($delim, array_map('preg_quote', $this->_keywords));

		// Loose ends?
		switch ($this->params->get('loose_ends'))
		{
			case 'left':
				$pattern = "({$keywords})\b";
			break;

			case 'right':
				$pattern = "\b({$keywords})";
			break;

			case 'both':
				$pattern = "({$keywords})";
			break;

			default:
				$pattern = "\b({$keywords})\b";
			break;
		}

		return "!{$pattern}!i";
	}

	/**
	 * Get score for entry ID
	 */
	private function _get_score($entry_id)
	{
		return isset($this->_results[$entry_id])
			? number_format(round($this->_results[$entry_id]['score'], 2), 2)
			: 0;
	}

	/**
	 * Get collection info for this search result
	 */
	private function _get_collection_info($entry_id)
	{
		// Init info
		$cols = array(
			'collection_id'    => '',
			'collection_name'  => '',
			'collection_label' => '',
			'excerpt'          => ''
		);

		if (isset($this->_results[$entry_id]))
		{
			$col_id = $this->_results[$entry_id]['collection_id'];

			if (isset($this->_collections[$col_id]))
			{
				$row = $this->_collections[$col_id];

				foreach ($row AS $key => $val)
				{
					if (array_key_exists($key, $cols))
					{
						$cols[$key] = $val;
					}
				}
			}
		}

		return $cols;
	}

	/**
	 * Get excerpt ID, the field ID to use as excerpt; 0 for title
	 */
	private function _get_excerpt_id($row)
	{
		$id = FALSE;

		if (is_numeric($row['mtt_search_excerpt']))
		{
			$id = $row['mtt_search_excerpt'];
		}
		elseif (isset($this->_excerpts[$row['channel_id']]))
		{
			$id = $this->_excerpts[$row['channel_id']];
		}

		return $id;
	}

	/**
	 * Add/modify entry in search index
	 *
	 * @access      private
	 * @param       string
	 * @param       string
	 * @return      string
	 */
	private function _create_excerpt($str = '', $fmt = 'none')
	{
		// Strip tags first
		$str = strip_tags($str);

		// Typography options
		$options = array(
			'text_format'   => $fmt,
			'html_format'   => 'safe',
			'auto_links'    => 'n',
			'almtt_img_url' => 'n'
		);

		// Format text
		$str = ee()->typography->parse_type($str, $options);

		// Strip again and trim it
		$str = trim(strip_tags($str));

		// Clean white space
		$str = preg_replace('/(&nbsp;|\s)+/', ' ', $str);

		// Limited string
		if ($length = (int) ee()->mtt_search_settings->get('excerpt_length'))
		{
			// Bail out if string is shorter than the amount of words given
			if ($length >= strlen($str) || $length >= str_word_count($str, 0, '1234567890')) return $str;

			if ($this->_keywords)
			{
				// Split the excerpt at the first keyword found
				$tmp = preg_split($this->_get_pattern(), $str, 2, PREG_SPLIT_DELIM_CAPTURE);

				// If we have a split, check the left part
				if ($tmp && count($tmp) >= 2)
				{
					// Amount of words to put on the left
					$left_count = round($length / 10);

					// Explode left words into an array
					$tmp[0] = explode(' ', $tmp[0]);

					// If there are more words on the left than allowed...
					if (count(array_filter($tmp[0])) > $left_count)
					{
						// ...slice off excess words...
						$tmp[0] = array_slice($tmp[0], -$left_count);

						// ...add horizontal ellipsis to the now first word and...
						$tmp[0][0] = '&#8230;'.$tmp[0][0];
					}

					// ...put it together again.
					$tmp[0] = implode(' ', $tmp[0]);

					// Now bring the whole excerpt together again...
					$str = implode('', $tmp);
				}
			}

			// ...and let EE's word limiter do the rest
			$str = ee()->functions->word_limiter($str, $length);
		}

		return $str;
	}

	/**
	 * Highlight keywords in given string
	 *
	 * @access      private
	 * @param       string
	 * @param       string
	 * @return      string
	 */
	private function _highlight($str)
	{
		if ($tag = ee()->mtt_search_settings->get('excerpt_hilite'))
		{
			// Case insensitive replace
			$str = preg_replace($this->_get_pattern(), "<{$tag}>$1</{$tag}>", $str);
		}

		return $str;
	}
}
// End of class Mtt_search_keywords_filter

// ====================================================================

/**
 * Single search term
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_term {

	public $term;
	public $exact       = FALSE;
	public $exclude     = FALSE;
	public $loose_left  = FALSE;
	public $loose_right = FALSE;

	/**
	 * Return the term for fulltext search
	 */
	public function get_fulltext_term($include = TRUE)
	{
		$term = $this->term;

		if ($this->exact) $term = '"'.$term.'"';
		if ($this->exclude) $term = '-'.$term;
		if ($this->loose_right) $term = $term.'*';
		if ($include && ! $this->exclude) $term = '+'.$term;

		return $term;
	}

	/**
	 * Return the term for fallback search
	 */
	public function get_fallback_term()
	{
		$term = " {$this->term} ";

		if ($this->loose_left) $term = ltrim($term);
		if ($this->loose_right) $term = rtrim($term);

		$like = $this->exclude ? 'NOT LIKE' : 'LIKE';

		return "{$like} '%{$term}%'";
	}
}

// ====================================================================

/**
 * Grouped search terms
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_term_group {

	/**
	 * Array of Mtt_search_term instances
	 */
	public $terms = array();

	/**
	 * Construct and set the terms
	 */
	public function __construct($terms = array())
	{
		$this->terms = $terms;
	}

}

// End of file lsf.keywords.php