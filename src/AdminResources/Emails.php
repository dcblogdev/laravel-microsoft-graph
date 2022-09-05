<?php

namespace Dcblogdev\MsGraph\AdminResources;

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Exception;

class Emails extends MsGraphAdmin
{
    private $userId;
    private $top;
    private $skip;
    private $subject;
    private $body;
    private $comment;
    private $id;
    private $to;
    private $cc;
    private $bcc;
    private $attachments;

    public function userid($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    public function to(array $to)
    {
        $this->to = $to;

        return $this;
    }

    public function cc(array $cc)
    {
        $this->cc = $cc;

        return $this;
    }

    public function bcc(array $bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function body($body)
    {
        $this->body = $body;

        return $this;
    }

    public function comment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function attachments(array $attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function top($top)
    {
        $this->top = $top;

        return $this;
    }

    public function skip($skip)
    {
        $this->skip = $skip;

        return $this;
    }

    public function get($params = [])
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        $top  = request('top', $this->top);
        $skip = request('skip', $this->skip);

        if ($params == []) {
            $params = http_build_query([
                '$top'     => $top,
                '$skip'    => $skip,
                '$count'   => 'true',
                '$orderby' => 'sentDateTime desc',
            ]);
        } else {
            $params = http_build_query($params);
        }

        //get messages from folderId
        $emails = MsGraphAdmin::get('users/'.$this->userId.'/messages?'.$params);

        $data = MsGraphAdmin::getPagination($emails, $top, $skip);

        return [
            'emails' => $emails,
            'total'  => $data['total'],
            'top'    => $data['top'],
            'skip'   => $data['skip'],
        ];
    }

    public function find($id)
    {
        if ($this->userId == null) {
            throw new Exception('userid is required.');
        }

        return MsGraphAdmin::get('users/'.$this->userId.'/messages/'.$id);
    }

    public function findAttachments($id)
    {
        return MsGraphAdmin::get('users/'.$this->userId.'/messages/'.$id.'/attachments');
    }

    public function findInlineAttachments($email)
    {
        $attachments = self::findAttachments($email['id']);

        //replace every case of <img='cid:' with the base64 image
        $email['body']['content'] = preg_replace_callback(
            '~cid.*?"~',
            function ($m) use ($attachments) {
                //remove the last quote
                $parts = explode('"', $m[0]);

                //remove cid:
                $contentId = str_replace('cid:', '', $parts[0]);

                //loop over the attachments
                foreach ($attachments['value'] as $file) {
                    //if there is a match
                    if ($file['contentId'] == $contentId) {
                        //return a base64 image with a quote
                        return 'data:'.$file['contentType'].';base64,'.$file['contentBytes'].'"';
                    }
                }
            },
            $email['body']['content']
        );

        return $email;
    }

    public function send()
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        if ($this->to == null) {
            throw new Exception('To is required.');
        }

        if ($this->subject == null) {
            throw new Exception('Subject is required.');
        }

        if ($this->comment != null) {
            throw new Exception('Comment is only used for replies and forwarding, please use body instead.');
        }

        return MsGraphAdmin::post('users/'.$this->userId.'/sendMail', self::prepareEmail());
    }

    public function reply()
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        if ($this->id == null) {
            throw new Exception('email id is required.');
        }

        if ($this->body != null) {
            throw new Exception('Body is only used for sending new emails, please use comment instead.');
        }

        return MsGraphAdmin::post('users/'.$this->userId.'/messages/'.$this->id.'/replyAll', self::prepareEmail());
    }

    public function forward()
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        if ($this->id == null) {
            throw new Exception('email id is required.');
        }

        if ($this->body != null) {
            throw new Exception('Body is only used for sending new emails, please use comment instead.');
        }

        return MsGraphAdmin::post('users/'.$this->userId.'/messages/'.$this->id.'/forward', self::prepareEmail());
    }

    public function delete($id)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::delete('users/'.$this->userId.'/messages/'.$id);
    }

    protected function prepareEmail()
    {
        $subject     = $this->subject;
        $body        = $this->body;
        $comment     = $this->comment;
        $to          = $this->to;
        $cc          = $this->cc;
        $bcc         = $this->bcc;
        $attachments = $this->attachments;

        $toArray = [];
        if ($to != null) {
            foreach ($to as $email) {
                $toArray[]['emailAddress'] = ['address' => $email];
            }
        }

        $ccArray = [];
        if ($cc != null) {
            foreach ($cc as $email) {
                $ccArray[]['emailAddress'] = ['address' => $email];
            }
        }

        $bccArray = [];
        if ($bcc != null) {
            foreach ($bcc as $email) {
                $bccArray[]['emailAddress'] = ['address' => $email];
            }
        }

        $attachmentarray = [];
        if ($attachments != null) {
            foreach ($attachments as $file) {
                $path = pathinfo($file);

                $attachmentarray[] = [
                    '@odata.type'  => '#microsoft.graph.fileAttachment',
                    'name'         => $path['basename'],
                    'contentType'  => mime_content_type($file),
                    'contentBytes' => base64_encode(file_get_contents($file)),
                ];
            }
        }

        $envelope = [];
        if ($subject != null) {
            $envelope['message']['subject'] = $subject;
        }
        if ($body != null) {
            $envelope['message']['body'] = [
                'contentType' => 'html',
                'content'     => $body,
            ];
        }
        if ($toArray != null) {
            $envelope['message']['toRecipients'] = $toArray;
        }
        if ($ccArray != null) {
            $envelope['message']['ccRecipients'] = $ccArray;
        }
        if ($bccArray != null) {
            $envelope['message']['bccRecipients'] = $bccArray;
        }
        if ($attachmentarray != null) {
            $envelope['message']['attachments'] = $attachmentarray;
        }
        if ($comment != null) {
            $envelope['comment'] = $comment;
        }

        return $envelope;
    }
}
