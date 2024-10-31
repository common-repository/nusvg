<?php

if (!class_exists('Nusvg_Collection')) {

    class Nusvg_Collection {

        public $Items = array();

        function format($s) {
            $res="";
            foreach ($this->Items as $Item) {
                $res.=$Item->format($s);
            }
            return  $res;
        }

    }

}