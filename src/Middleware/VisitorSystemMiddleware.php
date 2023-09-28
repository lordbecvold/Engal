<?php

namespace App\Middleware;

use App\Entity\Visitor;
use App\Util\EscapeUtil;
use App\Helper\EntityHelper;
use App\Helper\VisitorHelper;
use App\Util\VisitorInfoUtil;

/*
    Visitor system provides basic visitors managment
    Functions: insert new, update exist
*/

class VisitorSystemMiddleware
{ 

    private $entityHelper;
    private $visitorHelper;

    public function __construct(
        EntityHelper $entityHelper,
        VisitorHelper $visitorHelper 
    ) {
        $this->entityHelper = $entityHelper;
        $this->visitorHelper = $visitorHelper;
    }

    public function onKernelRequest(): void
    {
        // get data to insert
        $date = date('d.m.Y H:i:s');
        $os =VisitorInfoUtil::getOS();
        $ipAddress = VisitorInfoUtil::getIP();
        $browser = VisitorInfoUtil::getBrowser();
        $location = $this->visitorHelper->getLocation($ipAddress);

        // escape inputs
        $ipAddress = EscapeUtil::special_chars_strip($ipAddress);
        $browser = EscapeUtil::special_chars_strip($browser);
        $location = EscapeUtil::special_chars_strip($location);

        // visitor entity
        $visitorEntity = new Visitor();

        // get visitor ip address
        $address = VisitorInfoUtil::getIP();

        // check if visitor found in database
        if (!$this->entityHelper->isEntityExist(['ip_address' => $address], $visitorEntity)) {

            // insert new visitor
            $this->visitorHelper->insertNew($date, $ipAddress, $browser, $os, $location);
        } else {

            // update exist visitor
            $this->visitorHelper->updateVisitor($date, $ipAddress, $browser, $os);
        }
    }
}
