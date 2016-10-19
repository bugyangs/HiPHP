<?php

abstract class Library_ActionBase extends System_Core_Action {

    public function execute() {

        $this->_execute();

    }

    abstract public function _execute();
}