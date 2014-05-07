<?php

define('FUNKY_MAILER_PATH', TEST_DIR.DS."/fixtures/data/path.with.dots");

class FunkyPathMailer extends AkActionMailer
{
    public $templateRoot = FUNKY_MAILER_PATH;

    public function multipart_with_template_path_with_dots() {
        $this->setRecipients($recipient);
        $this->setSubject("Have a lovely picture");
        $this->setFrom("Chad Fowler <chad@example.com>");
        $this->addAttachment(array('content_type' => "image/jpeg"));
        $this->setBody("not really a jpeg, we're only testing, after all");
    }
}

?>