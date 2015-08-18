<?php

class block_twitter_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        $mform->addElement('header', 'configheader', get_string('twitter_settings', 'block_twitter'));

		$types = array(1 => 'Username', 2 => 'List' );
		$mform->addElement('select', 'config_type', get_string('twitter_type', 'block_twitter'), $types);

        $mform->addElement('text', 'config_username', get_string('username_name', 'block_twitter'));
        $mform->setType('config_username', PARAM_MULTILANG);        

        $mform->addElement('text', 'config_list', get_string('list_name', 'block_twitter'));
        $mform->setType('config_list', PARAM_MULTILANG);       

 		$mform->disabledIf('config_list', 'config_type', 'eq', 1);

        $mform->addElement('text', 'config_count', get_string('count', 'block_twitter'));
        $mform->setType('count', PARAM_MULTILANG);   
    }
}
