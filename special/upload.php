<?php 
$title = 'Upload file';
require_once __DIR__ . "/../pages.php";
if (file_exists(__DIR__ . "/../licenses.json")) $licenses = json_decode(file_get_contents(__DIR__ . "/../licenses.json"));
else $licenses = array(
    "Creative Commons Attribution-ShareAlike 4.0" => "CC BY-SA 4.0",
    "Creative Commons Attribution 4.0" => "CC BY 4.0",
    "Creative Commons Attribution-ShareAlike 3.0" => "CC BY-SA 3.0",
    "Creative Commons Attribution 3.0" => "CC BY 3.0",
    "Creative Commons Attribution-ShareAlike 2.0" => "CC BY-SA 2.0",
    "Creative Commons Attribution 2.0" => "CC BY 2.0",
    "Creative Commons Attribution-ShareAlike 1.0" => "CC BY-SA 1.0",
    "Creative Commons Attribution 1.0" => "CC BY 1.0",
    "Public domain" => "PD",
    "GFDL 1.3" => "FDL-1.3",
    "Other free cultural license (in description)" => "Other"
);
function upload($licenses) {
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
        $license = array_flip($licenses)[$_POST['license']];
        modifyPage("File:$destname", "## License\nThis file's license: **{$license}**\n## Description\n" . $_POST['desc'], "Adding file description page while uploading file {$destname}.");
        header('Location: index.php?title=File:' . urlencode($destname));
        exit;
    } else {
        ?><div class="error">An error occurred while uploading your file. Sorry! This could be because your file was over the maximum file size permitted by the server, which should be shown below.</div><?php 
        return;
    }
}
if (isset($_POST['upload'])) upload($licenses);
?>
<p>You can use this form to upload files to the wiki. Please do not violate copyright by
   making unauthorized copies of files found on the Internet without permission from the 
   creator, as that will force us to take your file down.</p>
<p>Note that due to technical restrictions, the maximum file size is <strong><?php echo ini_get('upload_max_filesize'); ?></strong></p>
<h2>File upload form</h2>
<form action="index.php?title=Special:upload" method="post" enctype="multipart/form-data">
<style>#page-content label, #page-content input, #page-content textarea { box-sizing: border-box; display: block; } </style>
<label>Choose a file to upload:
    <input type="file" name="file" required="required" />
</label>
<label>Location of file on server (MUST BE UNIQUE): <input name="destname" required="required" value="<?php if (isset($_POST['destname'])) echo htmlspecialchars($_POST['destname']); ?>" /></label>
<h2>Details</h2>
<h3>Copyright</h3>
<p>We need some information to determine if we are authorized to use this file.</p>
<label>
    What license is this file available under?
    <select required="required" name="license">
    <option disabled="disabled" selected="selected">Choose a license</option>
    <?php 
    if (file_exists(__DIR__ . "/../licenses.json")) $licenses = json_decode(file_get_contents(__DIR__ . "/../licenses.json"));
    else $licenses = array(
        "Creative Commons Attribution-ShareAlike 4.0" => "CC BY-SA 4.0",
        "Creative Commons Attribution 4.0" => "CC BY 4.0",
        "Creative Commons Attribution-ShareAlike 3.0" => "CC BY-SA 3.0",
        "Creative Commons Attribution 3.0" => "CC BY 3.0",
        "Creative Commons Attribution-ShareAlike 2.0" => "CC BY-SA 2.0",
        "Creative Commons Attribution 2.0" => "CC BY 2.0",
        "Creative Commons Attribution-ShareAlike 1.0" => "CC BY-SA 1.0",
        "Creative Commons Attribution 1.0" => "CC BY 1.0",
        "Public domain" => "PD",
        "GFDL 1.3" => "FDL-1.3",
        "Other free cultural license (in description)" => "Other"
    );
    foreach ($licenses as $label => $license) {
        ?><option <?php 
        if ($_POST['license'] === $license) { ?>selected="selected" <?php } 
        ?>value="<?php echo htmlspecialchars($license); ?>"><?php echo htmlspecialchars($label); ?></option><?php
    }
    ?>
    </select>
</label>
<h3>Description</h3>
<label>Enter a few details about your file:
    <textarea name="desc" rows="10" style="width: 100%;"><?php if (isset($_POST['desc'])) echo htmlspecialchars($_POST['desc']); ?></textarea>
</label>
<input type="submit" name="upload" value="Upload this file" />
</form>