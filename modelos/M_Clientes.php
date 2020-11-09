<?php

class M_Clientes extends \DB\SQL\Mapper {
    public function __construct() {
		parent::__construct( \Base::instance()->get('DB'), 'clientes' );
	}
}