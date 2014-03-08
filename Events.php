<?php
/**
 * Created by PhpStorm.
 * User: petwise
 * Date: 3/7/14
 * Time: 12:41 PM
 */

namespace RemindCloud;

use Mailgun\Mailgun;

class Events extends Mailgun
{

    protected $domain;

    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    public function getEvents($mgClient)
    {
        $queryString = array(
            'begin'     => 'Fri, 3 May 2013 09:00:00 -0000',
            'to'        => 'stevensjoshuac@gmail.com',
            'ascending' => 'yes',
            'limit'     => 25,
            'pretty'    => 'yes'
        );

        # Make the call to the client.
        $result       = $mgClient->get("$this->domain/events", $queryString);
        $results      = $result->http_response_body->items;
        $propertyName = 'message-id';

        foreach ($results as $event)
        {
            $next[] = array(
                'messageId'  => $event->message->headers->{$propertyName},
                'event'      => $event->event,
                'recipient' => $event->recipient
        );
        }
        $nexts = json_encode($next);
        return $nexts;
    }

} 