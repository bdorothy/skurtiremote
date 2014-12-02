<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include super model
if ( ! class_exists('Mtt_search_model'))
{
	require_once(PATH_THIRD.'mtt_search/model.mtt_search.php');
}

/**
 * Mtt Search Index Model class
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_index_model extends Mtt_search_model {

	// --------------------------------------------------------------------
	// CONSTANTS
	// --------------------------------------------------------------------

	const WEIGHT_SEPARATOR = ' | ';

	// --------------------------------------------------------------------

	/**
	 * Encountered fields
	 *
	 * @access      private
	 * @var         array
	 */
	private $fields = array();

	/**
	 * Encountered collections and their site ids
	 *
	 * @access      private
	 * @var         array
	 */
	private $collections = array();

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access      public
	 * @return      void
	 */
	function __construct()
	{
		// Call parent constructor
		parent::__construct();

		// Initialize this model
		$this->initialize(
			'mtt_search_indexes',
			array(
				'collection_id',
				'entry_id'
			),
			array(
				'site_id'    => 'int(4) unsigned NOT NULL',
				'index_text' => 'LONGTEXT NOT NULL',
				'index_date' => 'int(10) unsigned NOT NULL'
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Installs given table and adds indexes to it
	 *
	 * @access      public
	 * @return      void
	 */
	public function install()
	{
		// Call parent install
		parent::install();

		// Force MyISAM
		ee()->db->query("ALTER TABLE {$this->table()} ENGINE = MYISAM");

		// Add indexes to table
		ee()->db->query("ALTER TABLE {$this->table()} ADD INDEX (`collection_id`)");
		ee()->db->query("ALTER TABLE {$this->table()} ADD INDEX (`site_id`)");
		ee()->db->query("ALTER TABLE {$this->table()} ADD FULLTEXT (`index_text`)");
	}

	// --------------------------------------------------------------

	/**
	 * Builds an index for a given collection and entry
	 *
	 * @access      public
	 * @param       array
	 * @param       array
	 * @return      void
	 */
	public function build($collection = array(), $entry = array())
	{
		// --------------------------------------
		// Check parameters
		// --------------------------------------

		$collection_id = $collection['collection_id'];
		$settings = $collection['settings'];

		if ( ! is_array($settings))
		{
			$settings = mtt_search_decode($settings, FALSE);
		}

		$settings = array_filter($settings);
		$entry_id = $entry['entry_id'];

		// --------------------------------------
		// Load addon/fieldtype files
		// --------------------------------------

		ee()->load->library('addons');

		// Include EE Fieldtype class
		if ( ! class_exists('EE_Fieldtype'))
		{
			include_once (APPPATH.'fieldtypes/EE_Fieldtype'.EXT);
		}

		// --------------------------------------
		// Initiate fieldtypes var
		// --------------------------------------

		static $fieldtypes;

		// Init field ids
		$field_ids = array();

		// Set fieldtypes
		if ($fieldtypes === NULL)
		{
			$fieldtypes = ee()->addons->get_installed('fieldtypes');
		}

		// --------------------------------------
		// Get the field ids for these settings, minus title
		// --------------------------------------

		foreach (array_filter(array_keys($settings)) AS $field_id)
		{
			if (is_numeric($field_id)) $field_ids[] = $field_id;
		}

		// --------------------------------------
		// Check for ids we haven't dealt with yet
		// --------------------------------------

		if ($not_encountered = (array_diff($field_ids, array_keys($this->fields))))
		{
			// Get the details for given fields
			$query = ee()->db->select()
			       ->from('channel_fields')
			       ->where_in('field_id', $not_encountered)
			       ->get();

			foreach ($query->result_array() AS $row)
			{
				// Shortcut to fieldtype
				$ftype = $fieldtypes[$row['field_type']];

				// Include the file if it doesn't yet exist
				if ( ! class_exists($ftype['class']))
				{
					require $ftype['path'].$ftype['file'];
				}

				// Record it so we don't query again
				$this->fields[$row['field_id']] = TRUE;

				// Only initiate the fieldtypes that have the necessary method
				// Either mtt_search_index or third_party_search_index
				if (method_exists($ftype['class'], 'mtt_search_index') || method_exists($ftype['class'], 'third_party_search_index'))
				{
					// Initiate this fieldtype
					$this->fields[$row['field_id']] = new $ftype['class'];

					// Decode settings
					if ($field_settings = @unserialize(base64_decode($row['field_settings'])))
					{
						$row = array_merge($row, $field_settings);
					}

					// Add details to settings
					$row['mtt_search_collection_id'] = $collection_id;
					$row['field_name'] = 'field_id_'.$row['field_id'];
					$row['search_index_method'] = (method_exists($ftype['class'], 'mtt_search_index') ? 'mtt' : 'third_party').'_search_index';

					// Set this instance's settings
					$this->fields[$row['field_id']]->settings = $row;
				}
			}
		}

		// --------------------------------------
		// Init text array which will contain the index
		// --------------------------------------

		$text = array();

		// --------------------------------------
		// Loop through settings and add weight to field by repeating string
		// --------------------------------------

		foreach ($settings AS $field_id => $field_weight)
		{
			// --------------------------------------
			// Determine proper field id name
			// --------------------------------------

			$field_name = is_numeric($field_id) ? 'field_id_'.$field_id : $field_id;

			// --------------------------------------
			// Check fieldtype
			// --------------------------------------

			if (array_key_exists($field_name, $entry))
			{
				if (array_key_exists($field_id, $this->fields) && is_object($this->fields[$field_id]))
				{
					// Update entry id for this fieldtype
					$this->fields[$field_id]->settings['entry_id'] = $entry_id;

					// Which method should we use
					$method = $this->fields[$field_id]->settings['search_index_method'];

					// If fieldtype exists, it will have the correct method, so call that
					$str = $this->fields[$field_id]->$method($entry[$field_name]);
				}
				else
				{
					// If it doesn't, just use the raw data
					$str = $entry[$field_name];
				}
			}

			// --------------------------------------
			// If we have a string or array of strings,
			// add it to the text array
			// --------------------------------------

			if ( ! empty($str))
			{
				// Force output into an array
				if ( ! is_array($str)) $str = array($str);

				// And clean/filter it
				$str = array_filter(array_map('mtt_clean_string', $str));

				// Then add each line to text
				foreach ($str AS $s)
				{
					// Create pipe-separated weighted string
					$text[] = trim(
						self::WEIGHT_SEPARATOR.str_repeat($s.self::WEIGHT_SEPARATOR, $field_weight)
					);
				}
			}
		}

		// --------------------------------------
		// Keep track of collection and their site ids
		// --------------------------------------

		if ( ! isset($this->collections[$collection_id]))
		{
			$this->collections[$collection_id]
				= isset($collection['site_id'])
				? $collection['site_id']
				: $this->site_id;
		}

		// --------------------------------------
		// Data to insert
		// --------------------------------------

		$data = array(
			'collection_id' => $collection_id,
			'entry_id'      => $entry_id,
			'site_id'       => $this->collections[$collection_id],
			'index_text'    => implode("\n", $text),
			'index_date'    => ee()->localize->now
		);

		// --------------------------------------
		// 'mtt_search_update_index' hook
		// - Add additional attributes to the index
		// --------------------------------------

		if (ee()->extensions->active_hook('mtt_search_update_index') === TRUE)
		{
			$ext_data = ee()->extensions->call('mtt_search_update_index', $data, $entry);

			if (is_array($ext_data) && ! empty($ext_data))
			{
				$data = array_merge($data, $ext_data);
			}
		}

		// --------------------------------------
		// Get insert sql
		// --------------------------------------

		$sql = ee()->db->insert_string($this->table(), $data);

		// --------------------------------------
		// Change insert to replace to update existing entry
		// --------------------------------------

		ee()->db->query(preg_replace('/^INSERT/', 'REPLACE', $sql));
	}

	// --------------------------------------------------------------

	/**
	 * Get oldest index for given collection or all collections
	 *
	 * @access      public
	 * @param       int
	 * @param       array
	 * @return      void
	 */
	public function get_oldest_index($collection_id = FALSE)
	{
		ee()->db->select('collection_id, MIN(index_date) AS index_date')
		             ->from($this->table())
		             ->where('site_id', $this->site_id)
		             ->group_by('collection_id');

		// Limit by given collection
		if ($collection_id)
		{
			ee()->db->where('collection_id', $collection_id);
		}

		$query = ee()->db->get();

		// Return array of collection_id => index_date
		return mtt_flatten_results($query->result_array(), 'index_date', 'collection_id');
	}

	// --------------------------------------------------------------

	/**
	 * Optimize the index table
	 *
	 * @access     public
	 * @return     void
	 */
	public function optimize()
	{
		ee()->db->query('OPTIMIZE TABLE '.$this->table());
	}

} // End class

/* End of file Mtt_search_index_model.php */