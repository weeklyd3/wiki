<?php
/*
This file is part of weeklyd3's wiki software.

weeklyd3's wiki software is free software: you can redistribute 
it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either 
version 3 of the License, or (at your option) any later version.

weeklyd3's wiki software is distributed in the hope that it will 
be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public 
License along with weeklyd3's wiki software. If not, see 
<https://www.gnu.org/licenses/>. 
*/
 
$title = 'Upload file';
if (!isset($_SESSION['username'])) {
    $title = 'Please log in';
    ?><div class="error"><?php echo sysmsgPlain('upload-please-login'); ?></div><?php
    return;
}
require_once __DIR__ . "/../pages.php";
$licenses = explode("\n", sysmsgPlain('license-list'));
function upload() {
    $destname = cleanFilename($_POST['destname']);
    if (substr($destname, -4) === '.php') {
        ?><div class="error">Due to people uploading PHP files and then linking to them to execute them, you cannot upload PHP files. Sorry. Try giving it another extension like txt.</div><?php
        return;
    }
    if (!is_dir(__DIR__ . "/../files/live")) mkdir(__DIR__ . "/../files/live", 0777, true);
    if (file_exists(__DIR__ . "/../files/live/$destname")) {
        ?><div class="error">This file seems to already <a href="index.php?title=File:<?php echo htmlspecialchars(urlencode($destname)); ?>">exist</a>.</div><?php
        return;
    }
    if (move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . "/../files/live/$destname")) {
        $license = $_POST['license'];
        modifyPage("File:$destname", "## License\nThis file's license: **{$license}**\n## Description\n" . $_POST['desc'], "Adding file description page while uploading file {$destname}.");
        header('Location: index.php?title=File:' . urlencode($destname));
        exit;
    } else {
        ?><div class="error"><?php echo sysmsgPlain('file-upload-failed'); ?></div><?php 
        return;
    }
}
if (isset($_POST['upload'])) upload();
echo sysmsg("upload-header", ini_get('upload_max_filesize'));
?>
<form action="index.php?title=Special:upload" method="post" enctype="multipart/form-data">
<style>#page-content label, #page-content input, #page-content textarea { box-sizing: border-box; display: block; } </style>
<label><span class="help" title="<?php echo sysmsgPlain('file-upload-label-one-help'); ?>"><?php echo sysmsgPlain('file-upload-help'); ?></span> <?php echo sysmsgPlain('file-upload-label-one'); ?>
    <input type="file" name="file" required="required" />
</label>
<label><span class="help" title="<?php echo sysmsgPlain('file-upload-label-two-help'); ?>"><?php echo sysmsgPlain('file-upload-help'); ?></span> <?php echo sysmsgPlain('file-upload-label-two'); ?>
    <input name="destname" required="required" value="<?php if (isset($_POST['destname'])) echo htmlspecialchars($_POST['destname']); ?>" /></label>
<?php echo sysmsg('file-upload-details-copyright'); ?>
<label>
    <span class="help" title="<?php echo sysmsgPlain('file-upload-license-dropdown-help'); ?>"><?php echo sysmsgPlain('file-upload-help'); ?></span>
    <?php echo sysmsgPlain('license-dropdown-label'); ?>
    <select required="required" name="license">
    <option disabled="disabled" selected="selected" value=""><?php echo sysmsgPlain('file-upload-license-placeholder'); ?></option>
    <?php 
    $licenses = explode("\n", sysmsgPlain('license-list'));
    foreach ($licenses as $license) {
        ?><option <?php 
        if ($_POST['license'] === $license) { ?>selected="selected" <?php } 
        ?>value="<?php echo htmlspecialchars($license); ?>"><?php echo htmlspecialchars($license); ?></option><?php
    }
    ?>
    </select>
</label>
<?php echo sysmsg('file-upload-description-heading'); ?>
<label><span class="help" title="<?php echo sysmsgPlain('file-upload-description-help'); ?>"><?php echo sysmsgPlain('file-upload-help'); ?></span> <?php echo sysmsgPlain('file-upload-description-label'); ?>
    <textarea name="desc" rows="10" style="width: 100%;"><?php if (isset($_POST['desc'])) echo htmlspecialchars($_POST['desc']); ?></textarea>
</label>
<input type="submit" name="upload" value="<?php echo sysmsgPlain('file-upload-submit'); ?>" />
</form>