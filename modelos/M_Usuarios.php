<?php

class M_Usuarios extends \DB\SQL\Mapper {
    public function __construct() {
		parent::__construct( \Base::instance()->get('DB'), 'usuarios' );
	}
}