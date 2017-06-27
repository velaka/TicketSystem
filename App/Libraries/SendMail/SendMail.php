<?php 

namespace App\Libraries\SendMail;

use App\Libraries\PHPMailer\PHPMailer;

class SendMail {
    
    protected $app;
    private $transport;
    private $mailer;
    private $message;
    
    public function __construct()
    {
        $this->app = \Yee\Yee::getInstance();
        $this->settings = $this->app->config('mailer');
    }
    
    public function send( $mail_data )
    {
        
       
        
        //Create a new PHPMailer instance
        $mail = new PHPMailer;
        
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0; 
        
        //Ask for HTML-friendly debug output
        $mail->Debugoutput = 'html';
        
        //Set the hostname of the mail server
        $mail->Host = $this->settings[ 'mail.host' ];
        
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = $this->settings[ 'mail.port' ];
        
        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = $this->settings[ 'mail.tls' ];
        
        //Whether to use SMTP authentication
        $mail->SMTPAuth = $this->settings[ 'mail.smtp.auth' ];
        
        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = $this->settings['mail.user'];
        
        //Password to use for SMTP authentication
        $mail->Password = $this->settings['mail.pass'];
        
        //Set who the message is to be sent from
        $mail->setFrom( $mail_data['from.email'], $mail_data['from.name'] );
        
        //Set an alternative reply-to address
        //$mail->addReplyTo('replyto@example.com', 'First Last');
        
        //Set who the message is to be sent to
        $mail->addAddress( $mail_data['to'], $mail_data['to']);
        
        //Set the subject line
        $mail->Subject = $mail_data['subject'];
        
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML(  $mail_data['message'] );
        
        //Replace the plain text body with one created manually
        $mail->AltBody = 'This is a plain-text message body';
        
        //Attach an image file
        //$mail->addAttachment('images/phpmailer_mini.png');
        
        //send the message, check for errors
        if ( !$mail->send() ) 
        {
            return false;
        } else {
            return true;
        }
    }
    
    public function renderMail( $template, $data )
    {
    
        $templatePathname = $this->app->view->getTemplatePathname( $template );
    
        if (!is_file($templatePathname)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }
    
        //$data = array_merge( $this->app->view->data->all(), (array) $data);
    
        //extract( $data );
    
        $tpl = file_get_contents( $templatePathname );
         
        foreach( $data['templateData'] as $key => $value )
        {
            $tpl = str_ireplace('{%'.$key.'%}', $value, $tpl );
        }
        return $tpl;
    }
    
}