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

function getExport($var) {
    ob_start();
    var_export($var);
    return ob_get_clean();
}
if (file_exists(__DIR__ . '/../options.php')) {
    ?><p>Your wiki seems to already be installed. To reinstall it, please delete the options.php file generated by the installer.</p><?php
    return;
}
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../pages.php';
$options = array(
    "settings" => array(
        "type" => "label",
        "default" => "Basic information"
    ),
    "version" => array(
        "description" => "Software version:",
        "type" => "const",
        "default" => version
    ),
    "dir" => array(
        "description" => "Install directory",
        "type" => "const",
        "default" => realpath(__DIR__ . '/../')
    ),
    "sitename" => array(
        "type" => "text",
        "description" => "Site name:",
        "default" => "My test wiki"
    ),
    "subheading" => array(
        "type" => "text",
        "description" => "Subheading (the small text below the header)",
        "default" => $subheading
    ),
    "admin" => array(
        "type" => "label",
        "default" => "Admin stuff"
    ),
    "adminUserGroup" => array(
        "type" => "text",
        "description" => "The name of the 'administrators' group.",
        "default" => "administrators"
    ),
    "newUserGroup" => array(
        "type" => "text",
        "description" => "The name of the 'new users' group.",
        "default" => "new users"
    ),
    "username" => array(
        "type" => "text",
        "description" => "Admin username"
    ),
    "password" => array(
        "type" => "password",
        "description" => "Admin password"
    ),
    "code" => array(
        "type" => "label",
        "default" => "Boring code stuff"
    ),
    "footer" => array(
        "type" => "textarea",
        "description" => "Site footer code",
        "default" => file_get_contents(__DIR__ . '/../footer.html')
    ),
    "extensions" => array(
        "type" => "label",
        "default" => "Choose extensions to enable"
    )
);
$extensions = array_diff(scandir('extensions/'), array('.', '..'));
foreach ($extensions as $extension) {
    if (!is_dir("extensions/$extension")) continue;
    $options["extension-$extension"] = array(
        "type" => "checkbox",
        "description" => "Enable extension $extension"
    );
}
$title = 'Set up your wiki';
if (isset($_POST['install'])) {
    $title = 'Installing wiki';
    ?><p>Installing the wiki...</p><ul><?php
    $settingsFileText = file_get_contents(__DIR__ . '/../optionsWarning.php') . "\n";
    foreach ($_POST as $name => $value) {
        if ($name === 'footer') {
            ?><li>Writing footer code... <?php
            fwrite(fopen(__DIR__ . '/../footer.html', 'w+'), $_POST['footer']);
            ?>DONE</li><?php
            continue;
        }
        if (substr($name, 0, strlen('extension-')) === 'extension-') {
            $ext = substr($name, strlen('extension-'));
            ?><li>Adding extension: <?php echo htmlspecialchars($ext); ?></li><?php
            $extName = getExport($ext);
            $settingsFileText .= "// Loads extension $ext.\nloadExtension($extName);\n";
            continue;
        }
        if ($name === 'username' || $name === 'install' || $name === 'password') continue;
        ?><li>Adding setting: <?php echo htmlspecialchars($name); ?></li><?php
        $settingsFileText .= "// " . $options[$name]['description'];
        $settingsFileText .= "\n";
        $settingsFileText .= '$' . $name . " = " . getExport($value) . ';';
        $settingsFileText .= "\n";
    }
    $settingsFileText .= file_get_contents('../optionsWarningBottom.php');
    ?><li>Making administrator account... <?php 
    createAccount($_POST['username'], $_POST['password'], array('administrators'));
    ?>DONE</li>
    <li>Logging in to administrator account... <?php login($_POST['username'], $_POST['password']); ?>DONE</li>
<li>Writing settings file... <?php fwrite(fopen(__DIR__ . "/../options.php", "w+"), $settingsFileText); ?>DONE</li>
<li>Creating main page... <?php modifyPage("Main Page", file_get_contents(__DIR__ . '/../defaultMainPage.txt'), 'Create main page (initiated during installation)');
?>DONE</li>
</ul>
<p>The installation has completed. You can now <a href="index.php">browse your wiki</a>.</p>
<?php
    return;
}
?>
<p>This is the setup utility. If you are seeing this, then your 
    wiki has not been set up yet.</p>
<p>This is the only page of options. No, really!</p>
<form action="index.php?title=Special:install" method="post">
<table>
<?php 
$subheading = 'You are not done yet';
foreach ($options as $label => $option) {
    if ($option['type'] === 'label') {
        ?><tr><th scope="row" colspan="2"><?php echo htmlspecialchars($option['default']); ?></th></tr><?php
        continue;
    }
    ?><tr>
        <th class="install-label"><label for="option<?php echo htmlspecialchars($label); ?>"><?php echo $option['description']; ?></label></th>
        <td><?php 
        switch ($option['type']) {
            case 'checkbox': 
                ?>
                <input name="<?php echo htmlspecialchars($label); ?>" id="option<?php echo htmlspecialchars($label); ?>" type="checkbox" /><?php
                break;
            case 'textarea':
                ?><textarea required="required" rows="5" cols="50" name="<?php echo htmlspecialchars($label); ?>" id="option<?php echo htmlspecialchars($label); ?>"><?php if (isset($option['default'])) echo htmlspecialchars($option['default']); ?></textarea><?php
                break;
            case 'text':
                ?><input required="required" name="<?php echo htmlspecialchars($label); ?>" id="option<?php echo htmlspecialchars($label); ?>" value="<?php if (isset($option['default'])) echo htmlspecialchars($option['default']); ?>" /><?php
                break;
            case 'password':
                ?><input required="required" type="password" name="<?php echo htmlspecialchars($label); ?>" id="option<?php echo htmlspecialchars($label); ?>" value="<?php if (isset($option['default'])) echo htmlspecialchars($option['default']); ?>" /><?php
                break;
            case 'const':
                ?><strong class="constant"><?php echo htmlspecialchars($option['default']); ?></strong><?php
        }
        ?></td>
    </tr><?php
}
?>
</table>
<input type="submit" value="Install the wiki" name="install" />
</form>