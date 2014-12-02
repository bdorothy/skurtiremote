<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Languages
{
	private $languages = array(
		'aa' => 'afar',
		'ab' => 'abkhazian',
		'af' => 'afrikaans',
		'am' => 'amharic',
		'ar' => 'arabic',
		'as' => 'assamese',
		'ay' => 'aymara',
		'az' => 'azerbaijani',
		'ba' => 'bashkir',
		'be' => 'byelorussian',
		'bg' => 'bulgarian',
		'bh' => 'bihari',
		'bi' => 'bislama',
		'bn' => 'bengali',
		'bo' => 'tibetan',
		'br' => 'breton',
		'ca' => 'catalan',
		'co' => 'corsican',
		'cs' => 'czech',
		'cy' => 'welsh',
		'da' => 'danish',
		'de' => 'german',
		'dz' => 'bhutani',
		'el' => 'greek',
		'en' => 'english',
		'eo' => 'esperanto',
		'es' => 'spanish',
		'et' => 'estonian',
		'eu' => 'basque',
		'fa' => 'persian',
		'fi' => 'finnish',
		'fj' => 'fiji',
		'fo' => 'faeroese',
		'fr' => 'french',
		'fy' => 'frisian',
		'ga' => 'irish',
		'gd' => 'gaelic',
		'gl' => 'galician',
		'gn' => 'guarani',
		'gu' => 'gujarati',
		'ha' => 'hausa',
		'hi' => 'hindi',
		'hr' => 'croatian',
		'hu' => 'hungarian',
		'hy' => 'armenian',
		'ia' => 'interlingua',
		'ie' => 'interlingue',
		'ik' => 'inupiak',
		'in' => 'indonesian',
		'is' => 'icelandic',
		'it' => 'italian',
		'iw' => 'hebrew',
		'ja' => 'japanese',
		'ji' => 'yiddish',
		'jw' => 'javanese',
		'ka' => 'georgian',
		'kk' => 'kazakh',
		'kl' => 'greenlandic',
		'km' => 'cambodian',
		'kn' => 'kannada',
		'ko' => 'korean',
		'ks' => 'kashmiri',
		'ku' => 'kurdish',
		'ky' => 'kirghiz',
		'la' => 'latin',
		'ln' => 'lingala',
		'lo' => 'laothian',
		'lt' => 'lithuanian',
		'lv' => 'latvian',
		'mg' => 'malagasy',
		'mi' => 'maori',
		'mk' => 'macedonian',
		'ml' => 'malayalam',
		'mn' => 'mongolian',
		'mo' => 'moldavian',
		'mr' => 'marathi',
		'ms' => 'malay',
		'mt' => 'maltese',
		'my' => 'burmese',
		'na' => 'nauru',
		'ne' => 'nepali',
		'nl' => 'dutch',
		'no' => 'norwegian',
		'oc' => 'occitan',
		'om' => 'oromo',
		'or' => 'oriya',
		'pa' => 'punjabi',
		'pl' => 'polish',
		'ps' => 'pashto, pushto',
		'pt' => 'portuguese',
		'qu' => 'quechua',
		'rm' => 'rhaeto-romance',
		'rn' => 'kirundi',
		'ro' => 'romanian',
		'ru' => 'russian',
		'rw' => 'kinyarwanda',
		'sa' => 'sanskrit',
		'sd' => 'sindhi',
		'sg' => 'sangro',
		'sh' => 'serbo-croatian',
		'si' => 'singhalese',
		'sk' => 'slovak',
		'sl' => 'slovenian',
		'sm' => 'samoan',
		'sn' => 'shona',
		'so' => 'somali',
		'sp' => 'spanish',
		'sq' => 'albanian',
		'sr' => 'serbian',
		'ss' => 'siswati',
		'st' => 'sesotho',
		'su' => 'sudanese',
		'sv' => 'swedish',
		'sw' => 'swahili',
		'ta' => 'tamil',
		'te' => 'tegulu',
		'tg' => 'tajik',
		'th' => 'thai',
		'ti' => 'tigrinya',
		'tk' => 'turkmen',
		'tl' => 'tagalog',
		'tn' => 'setswana',
		'to' => 'tonga',
		'tr' => 'turkish',
		'ts' => 'tsonga',
		'tt' => 'tatar',
		'tw' => 'twi',
		'uk' => 'ukrainian',
		'ur' => 'urdu',
		'uz' => 'uzbek',
		'vi' => 'vietnamese',
		'vo' => 'volapuk',
		'wo' => 'wolof',
		'xh' => 'xhosa',
		'yo' => 'yoruba',
		'zh' => 'chinese',
		'zu' => 'zulu',
	);
	
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
	public function languages()
	{
		return $this->languages;
	}
	
	public function get_language_abbrev($language)
	{
		foreach ($this->languages as $abbrev => $full)
		{
			if (strtolower($language) === $full)
			{
				return $abbrev;
			}
		}
	}
	
	public function set_language($language)
	{
		if ( ! $language)
		{
			return;
		}
		
		if (is_array($language))
		{
			$language = (isset($language['language'])) ? $language['language'] : '';
		}
		
		$language = (isset($this->languages[$language])) ? $this->languages[$language] : $language;
		
		$this->EE->session->userdata['language'] = $language;
		
		if (isset($this->EE->cartthrob) && is_object($this->EE->cartthrob))
		{
			$this->EE->cartthrob->cart->set_customer_info('language', $language);
		}
		
		$this->EE->functions->set_cookie('language', $language, 60*60*24);
		
		$language_files = array(
			'cartthrob',
			'cartthrob_errors'
		);

		foreach ($language_files as $file)
		{
			if (($key = array_search($file.'_lang'.EXT, $this->EE->lang->is_loaded)) !== FALSE)
			{
				unset($this->EE->lang->is_loaded[$key]);
			}

			$this->EE->lang->loadfile($file, 'cartthrob');
		}
 	}
}