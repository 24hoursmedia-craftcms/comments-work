<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 04/06/2020
 */

namespace twentyfourhoursmedia\commentswork\interfaces;

interface AllowedInterface
{

    public function isAllowed() : bool;

    public function getMessage() : string;

}