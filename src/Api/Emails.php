<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Emails {

    public function emails($limit = 25, $skip = 0, $folderId = null)
    {
        $messageQueryParams = array (
            "\$skip" => $skip,
            "\$top" => $limit,
            "\$count" => "true",
        );

        if ($folderId == null) {
        	$folder = 'Inbox';
        } else {
        	$folder = $folderId;
        }

        //get inbox from folders list
        $folder = self::get("me/mailFolders?\$filter=startswith(displayName,'$folder')");

        //folder id
        $inbox = $folder['value'][0]['id'];

        //get messages from inbox folder
        $emails = self::get("me/mailFolders/$inbox/messages?".http_build_query($messageQueryParams));

        $total = $emails['@odata.count'];
		$previous = null;
		$next = null;
		if (isset($emails['@odata.nextLink'])) {
			$first = explode('$skip=', $emails['@odata.nextLink']);

			$skip = explode('&', $first[1]);
			$previous = $skip[0]-$limit;
			$next = $skip[0];

			if ($previous < 0) {
				$previous = 0;
			}

			if ($next == $total) {
				$next = null;
			}
		}

        return [
            'emails' => $emails,
            'total' => $total,
            'previous' => $previous,
            'next' => $next
        ];
    }

    public function emailAttachments($email_id)
    {
    	return self::get("me/messages/".$email_id."/attachments");
    }

    public function emailInlineAttachments($email)
    {
    	$attachments = self::emailAttachments($email['id']);

    	//replace every case of <img='cid:' with the base64 image
        $email['body']['content'] = preg_replace_callback(
            '~cid.*?"~',
            function($m) use($attachments) {

                //remove the last quote
                $parts = explode('"',$m[0]);

                //remove cid:
                $contentId = str_replace('cid:', '', $parts[0]);

                //loop over the attachments
                foreach ($attachments['value'] as $file) {
                    //if there is a match
                    if ($file['contentId'] == $contentId) {
                        //return a base64 image with a quote
                        return "data:".$file['contentType'].";base64,".$file['contentBytes'].'"';
                    }
                }
            },
            $email['body']['content']
        );

        return $email;
    }

    public function emailSend($subject, $message, $to, $cc, $bcc, $attachments = null)
    {
        //send an email to a draft
        $draft = self::post('me/messages', self::emailPrepare($subject, $message, $to, $cc, $bcc));

        if ($attachments != null) {
            foreach($attachments as $file) {
                //create an attachment and send to the draft message based on the message id
                $attachment = self::post('me/messages/'.$draft['id'].'/attachments', $file);
            }
        }

        //send the draft message now it's complete
        return self::post('me/messages/'.$draft['id'].'/send', []);
    }

    public function emailSendReply($id, $message, $to, $cc, $bcc, $attachments = null)
    {
        //send an email to a draft
        $draft = self::post("me/messages/$id/createReplyAll", self::prepareReply($message, $to, $cc, $bcc));

        if ($attachments != null) {
            foreach($attachments as $file) {
                //create an attachment and send to the draft message based on the message id
                $attachment = self::post('me/messages/'.$draft['id'].'/attachments', $file);
            }
        }

        //send the draft message now it's complete
        self::post('me/messages/'.$draft['id'].'/send', []);
    }

    public function emailSendForward($id, $message, $to, $cc, $bcc, $attachments = null)
    {
        //send an email to a draft
        $draft = self::post("me/messages/$id/createForward", self::emailPrepareReply($message, $to, $cc, $bcc));

        if ($attachments != null) {
            foreach($attachments as $file) {
                //create an attachment and send to the draft message based on the message id
                $attachment = self::post('me/messages/'.$draft['id'].'/attachments', $file);
            }
        }

        //send the draft message now it's complete
        self::post('me/messages/'.$draft['id'].'/send', []);
    }

    protected static function emailPrepare($subject, $message, $to, $cc = null, $bcc = null)
    {

        $parts = explode(',', $to);
        $toArray = [];
        foreach($parts as $to) {
            $toArray[]["emailAddress"] = ["address" => $to];
        }

        $ccArray = [];
        if ($cc != null) {
            $parts = explode(',', $cc);
            foreach($parts as $cc) {
                $ccArray[]["emailAddress"] = ["address" => $cc];
            }
        }

        $bccArray = [];
        if ($bcc != null) {
            $parts = explode(',', $bcc);
            foreach($parts as $bcc) {
                $bccArray[]["emailAddress"] = ["address" => $bcc];
            }
        }

        return [
            "subject" => $subject,
            "body" => [
                "contentType" => "html",
                "content" => $message
            ],
            "toRecipients" => $toArray,
            "ccRecipients" => $ccArray,
            "bccRecipients" => $bccArray
        ];
    }

    protected static function emailPrepareReply($message, $to, $cc = null, $bcc = null)
    {

        $parts = explode(',', $to);
        $toArray = [];
        foreach($parts as $to) {
            $toArray[]["emailAddress"] = ["address" => $to];
        }

        $ccArray = [];
        if ($cc != null) {
            $parts = explode(',', $cc);
            foreach($parts as $cc) {
                $ccArray[]["emailAddress"] = ["address" => $cc];
            }
        }

        $bccArray = [];
        if ($bcc != null) {
            $parts = explode(',', $bcc);
            foreach($parts as $bcc) {
                $bccArray[]["emailAddress"] = ["address" => $bcc];
            }
        }

        return [
            "comment" => $message,
            "toRecipients" => $toArray,
            "ccRecipients" => $ccArray,
            "bccRecipients" => $bccArray
        ];
    }
}
