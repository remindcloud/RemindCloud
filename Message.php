<?php

namespace RemindCloud;

use Mailgun\Mailgun;
use PDO;

class Message extends Mailgun
{

    protected $messageId;

    protected $mailgunId;

    protected $resultMsg;

    protected $result;

    protected $domain;

    protected $conn;

    protected $mgClient;

    public function __construct($domain, $mgClient, PDO $conn)
    {
        $this->domain   = $domain;
        $this->conn     = $conn;
        $this->mgClient = $mgClient;
    }

    public function queueBatch()
    {
        if ($stmt = $this->conn->prepare('SELECT id,subject,sender,senderName,body,recipient,recipientName FROM message WHERE mailgunId is NULL AND result is NULL AND is_processed is NULL'))
        {
            $stmt->execute();
            if ($stmt->rowCount() > 0)
            {
                $result = $stmt->fetchAll();
                foreach ($result as $row)
                {
                    $this->messageId = $row['id'];
                    $to              = $row['recipientName'] . "<" . $row['recipient'] . ">";
                    $from            = $row['senderName'] . "<" . $row['sender'] . ">";
                    $this->sendSingle($to, $from, $row['subject'], $row['body']);
                }
            }
            else
            {
                echo 'NO MESSAGES NEED TO BE SENT';
            }
        }
        else
        {
            die("ERROR: VALIDATE STATEMENT COULD NOT BE PREPARED");
        }
    }

    public function sendSingle($to, $from, $subject, $text)
    {
        # Make the call to the client.
        $result          = $this->mgClient->sendMessage($this->domain,
            array(
                'from'    => $from,
                'to'      => $to,
                'subject' => $subject,
                'html'    => $text
            ));
        $this->result    = $result->http_response_code;
        $this->resultMsg = $result->http_response_body->message;
        $this->mailgunId = $this->cleanString($result->http_response_body->id);
        if ($this->updateMessage()) echo 'Message Sent Successfully';
        //$result = json_encode($result);
        //return $response;
    }

    public function sendBatch($mgClient)
    {
        # Make the call to the client.
        $result = $mgClient->sendMessage($this->domain,
            array(
                'from'                => 'Excited User <info@remindcloud.com>',
                'to'                  => 'stevensjoshuac@gmail.com, joshua.stevens@avimark.net',
                'subject'             => 'Hello',
                'text'                => 'If you wish to unsubscribe,
                          click http://mailgun/unsubscribe/%recipient.id%',
                'recipient-variables' => '{"stevensjoshuac@gmail.com": {"id":1},
                                       "joshua.stevens@avimark.net": {"id": 2}}'
            ));
        return $result;
    }

    public function updateMessage()
    {
        try
        {
            $sql = "UPDATE message set mailgunId = :mailgunId, sentAt = :sentat, result = :result, is_processed = :is_processed where id = :messageId";
            if ($q = $this->conn->prepare($sql))
            {
                $q->execute(array(
                    ':mailgunId'    => $this->mailgunId,
                    ':sentat'       => date("Y-m-d h:i:s"),
                    ':result'       => $this->result,
                    ':is_processed' => '1',
                    ':messageId'    => $this->messageId
                ));
                if ($q->rowCount() > 0)
                {
                    return TRUE;
                }
                else
                {
                    return FALSE;
                }
            }
            else
            {
                die("ERROR: VALIDATE STATEMENT COULD NOT BE PREPARED");
            }

        } catch (PDOException $e)
        {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function cleanString($string)
    {
        $string = str_replace('<', ' ', $string);
        $string = str_replace('>', ' ', $string);
        return $string;

    }
}