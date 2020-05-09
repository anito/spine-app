<?php
foreach ( $notices as $key => $notice ) {
//    if (in_array($key, $dismissed_notices)) {
//        continue;
//    }
    ?>
    <div id="<?php echo $key; ?>" class="<?php echo $notice['class']; ?>">
        <h4>More Options will be available after installing the following Plugins:</h4>
        <div></div>
        <ul style="">
            <?php foreach( $notice['errors'] as $k => $error ) {
                ?>
                <li style="">
                    <span style="border-radius:2px;padding:5px;background:#ddd;"><?= $error['plugin_name']; ?></span> -> 
                    <span style="font-style:italic;"><?= $error['tab']['description']; ?></span>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
<?php } ?>