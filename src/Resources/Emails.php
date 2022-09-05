<?php

namespace Dcblogdev\MsGraph\Resources;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Exception;

class Emails extends MsGraph
{
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

    public function get($folderId = null, $params = [])
    {
        $top  = request('top', $this->top);
        $skip = request('skip', $this->skip);

        if ($top === null) {
            $top = 100;
        }

        if ($skip === null) {
            $skip = 0;
        }

        if ($params == []) {
            $params = http_build_query([
                '$top'   => $top,
                '$skip'  => $skip,
                '$count' => 'true',
            ]);
        } else {
            $params = http_build_query($params);
        }

        $folder = $folderId == null ? 'Inbox' : $folderId;

        //get inbox from folders list
        $folder = MsGraph::get("me/mailFolders?\$filter=startswith(displayName,'$folder')");

        if (isset($folder['value'][0])) {
            //folder id
            $folderId = $folder['value'][0]['id'];

            //get messages from folderId
            return MsGraph::get("me/mailFolders/$folderId/messages?".$params);
        } else {
            return throw new Exception('email folder not found');
        }
    }

    public function find($id)
    {
        return MsGraph::get('me/messages/'.$id);
    }

    public function findAttachments($id)
    {
        return MsGraph::get('me/messages/'.$id.'/attachments');
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
        if ($this->to == null) {
            throw new Exception('To is required.');
        }

        if ($this->subject == null) {
            throw new Exception('Subject is required.');
        }

        if ($this->comment != null) {
            throw new Exception('Comment is only used for replies and forwarding, please use body instead.');
        }

        return MsGraph::post('me/sendMail', self::prepareEmail());
    }

    public function reply()
    {
        if ($this->id == null) {
            throw new Exception('email id is required.');
        }

        if ($this->body != null) {
            throw new Exception('Body is only used for sending new emails, please use comment instead.');
        }

        return MsGraph::post('me/messages/'.$this->id.'/replyAll', self::prepareEmail());
    }

    public function forward()
    {
        if ($this->id == null) {
            throw new Exception('email id is required.');
        }

        if ($this->body != null) {
            throw new Exception('Body is only used for sending new emails, please use comment instead.');
        }

        return MsGraph::post('me/messages/'.$this->id.'/forward', self::prepareEmail());
    }

    public function delete($id)
    {
        return MsGraph::delete('me/messages/'.$id);
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
