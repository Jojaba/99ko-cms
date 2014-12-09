<?php defined('ROOT') OR exit('No direct script access allowed'); ?>
<br />		  
  <div class="row">
    <div class="large-6 columns">
      <label><?php echo lang("Admin mail"); ?></label>
      <input type="email" name="adminEmail" value="<?php echo $config['adminEmail']; ?>" />
    </div>
  </div>  

  <div class="row">
    <div class="large-6 columns">
      <label><?php echo lang("New admin password"); ?></label>
      <input type="password" name="adminPwd" value="" autocomplete="off" />
    </div>
    <div class="large-6 columns">
      <label><?php echo lang("Confirmation"); ?></label>
      <input type="password" name="adminPwd2" value="" autocomplete="off" />
    </div>
  </div>
  
  <button type="submit" class="button success radius"><?php echo lang("Save"); ?></button>