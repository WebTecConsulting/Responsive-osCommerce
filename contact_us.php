<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require('includes/languages/' . $language . '/contact_us.php');

  require 'includes/classes/' . 'captcha.php';
  $captcha = new Captcha();

  if (isset($_GET['action']) && ($_GET['action'] == 'send') && isset($_POST['formid']) && ($_POST['formid'] == $sessiontoken)) {
    $error = false;

    $name = tep_db_prepare_input($_POST['name']);
    $email_address = tep_db_prepare_input($_POST['email']);
    $enquiry = tep_db_prepare_input($_POST['enquiry']);
    $accept_condition = tep_db_prepare_input(isset($_POST['condition'])?$_POST['condition']:'');

    if (!tep_validate_email($email_address)) {
      $error = true;

      $messageStack->add('contact', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }
    
    if( !tep_not_null($enquiry) ) {
    	$error = true;

    	$messageStack->add('contact', ENTRY_ENQUIRY_ERROR_EMPTY);    	 
    } 

    if( !$captcha->check($_POST['captcha'])) {
    	$error = true;
    	
    	$messageStack->add('contact', ENTRY_CAPTCHA_ERROR);    	 
    }

    if( USE_DATASTORAGE_CONFIMATION == 'true'  && $accept_condition != 'on' ) {
    	$error = true;

    	$messageStack->add('contact', ENTRY_CONFIRM_DATASTORAGE_MESSAGE);    	 
    } 

    
    
    $actionRecorder = new actionRecorder('ar_contact_us', (tep_session_is_registered('customer_id') ? $customer_id : null), $name);
    if (!$actionRecorder->canPerform()) {
      $error = true;

      $actionRecorder->record(false);

      $messageStack->add('contact', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES') ? (int)MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES : 15)));
    }

    if ($error == false) {
      tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, EMAIL_SUBJECT, $enquiry, $name, $email_address);

      $actionRecorder->record();

      tep_redirect(tep_href_link('contact_us.php', 'action=success'));
    }
  }

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link('contact_us.php'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('contact') > 0) {
    echo $messageStack->output('contact');
  }

  if (isset($_GET['action']) && ($_GET['action'] == 'success')) {
?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-info"><?php echo TEXT_SUCCESS; ?></div>
  </div>

  <div class="pull-right">
    <?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', tep_href_link('index.php')); ?>
  </div>
</div>

<?php
  } else {
?>

<?php echo tep_draw_form('contact_us', tep_href_link('contact_us.php', 'action=send'), 'post', 'class="form-horizontal"', true); ?>

<div class="contentContainer">
  <div class="contentText">
  
    <p class="text-danger text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></p>
    <div class="clearfix"></div>

    <div class="form-group has-feedback">
      <label for="inputFromName" class="control-label col-sm-3"><?php echo ENTRY_NAME; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_input_field('name', NULL, 'required autofocus="autofocus" aria-required="true" id="inputFromName" placeholder="' . ENTRY_NAME_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputFromEmail" class="control-label col-sm-3"><?php echo ENTRY_EMAIL; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_input_field('email', NULL, 'required aria-required="true" id="inputFromEmail" placeholder="' . ENTRY_EMAIL_ADDRESS_TEXT . '"', 'email');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputEnquiry" class="control-label col-sm-3"><?php echo ENTRY_ENQUIRY; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_textarea_field('enquiry', 'soft', 50, 15, NULL, 'required aria-required="true" id="inputEnquiry" placeholder="' . ENTRY_ENQUIRY_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
  <div class="form-group has-feedback">
      <label for="inputEnquiry" class="control-label col-sm-3"></label>
      <div class="col-sm-9">
        <?php echo tep_image($captcha->getCaptcha(), 'captcha', CAPTCHA_WIDTH, CAPTCHA_HEIGHT, 'style="float:left"', false)
        	.'<span id="captcha"><a href="contact_us.php" title="Neu laden">' . tep_image( DIR_WS_CATALOG_IMAGES . 'icons/refresh.png' , 'Neu laden', 32, 32) . '</a></span><br />'; 
		#echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputEnquiry" class="control-label col-sm-3"><?php echo ENTRY_CAPTCHA_TEXT; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_input_field('captcha', '', 'required aria-required="true" id="inputFromCaptcha" placeholder="' . ENTRY_CAPTCHA_TEXT . '"', true, 'text', false);
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
<?php if( USE_DATASTORAGE_CONFIMATION == 'true' ) { ?>    
    <div class="form-group has-feedback">
      <label for="inputEnquiry" class="control-label col-sm-3"><?php echo ''; ?></label>
      <div class="col-sm-9">
        <?php
                echo tep_draw_checkbox_field('condition', '', false, 'required aria-required="true" id="inputFromCondition" placeholder=""') . ' ';
                echo ENTRY_CONFIRM_DATASTORAGE;
                echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
<?php } ?>
    
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'fa fa-send', null, 'primary', null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  }

  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
