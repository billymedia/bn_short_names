<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bn_short_names_ext
{
	public $settings = array();
	public $name = 'BN Short Names';
	public $version = '1.0.0';
	public $description = 'Displays the short names of your custom fields on the publish/edit page.';
	public $settings_exist = 'n';
	public $docs_url = 'http://barrettnewton.com';
	
	/**
	 * Extension_ext
	 * 
	 * @access	public
	 * @param	mixed $settings = ''
	 * @return	void
	 */
	public function __construct($settings = '')
	{
		$this->EE = get_instance();
		
		$this->settings = $settings;
		
		$this->classname = get_class($this);
	}
	
	/**
	 * activate_extension
	 * 
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$hook_defaults = array(
			'class' => $this->classname,
			'settings' => '',
			'version' => $this->version,
			'enabled' => 'y',
			'priority' => 10
		);
		
		$hooks[] = array(
			'method' => 'cp_js_end',
			'hook' => 'cp_js_end'
		);
		
		foreach ($hooks as $hook)
		{
			$this->EE->db->insert('extensions', array_merge($hook_defaults, $hook));
		}
	}
	
	/**
	 * update_extension
	 * 
	 * @access	public
	 * @param	mixed $current = ''
	 * @return	void
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		$this->EE->db->update('extensions', array('version' => $this->version), array('class' => $this->classname));
	}
	
	/**
	 * disable_extension
	 * 
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->EE->db->delete('extensions', array('class' => $this->classname));
	}
	
	/**
	 * settings
	 * 
	 * @access	public
	 * @return	void
	 */
	public function settings()
	{
		$settings = array();
		
		return $settings;
	}
	
	public function cp_js_end()
	{
		$this->EE->load->helper('array');
		$this->EE->load->library('security');
		
		//get $_GET from the referring page
		parse_str(parse_url(@$_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $get);
		
		$output = $this->EE->extensions->last_call;
		
		//check if we're on the publish page
		if (element('D', $get) == 'cp' && element('C', $get) == 'content_publish' && element('M', $get) == 'entry_form' && element('channel_id', $get))
		{
			$this->EE->db->select('channel_fields.field_id AS id, channel_fields.field_name AS name')
					->from('channel_fields')
					->join('channels', 'channels.field_group = channel_fields.group_id')
					->where('channels.channel_id', $this->EE->security->xss_clean(element('channel_id', $get)));
			
			$query = $this->EE->db->get();
			
			if ($query->num_rows())
			{
				$this->EE->load->library('javascript');
				$output .= '$(function(){$.each('.$this->EE->javascript->generate_json($query->result_array()).',function(i,f){$("#hold_field_"+f.id+" label:first span").append($("<span>{"+f.name+"}</span>").click(function(e){e.stopPropagation();}));});});'."\r\n";
			}
		}
		
		return $output;
	}
}

/* End of file ext.extension.php */
/* Location: ./system/expressionengine/third_party/extension/ext.extension.php */