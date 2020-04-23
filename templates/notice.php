<?php
foreach ($notices as $key => $notice) {
//    if (in_array($key, $dismissed_notices)) {
//        continue;
//    }
    ?>
    <div id="<?php echo $key; ?>" class="<?php echo $notice['class']; ?>" data-key="<?php echo $key; ?>">
        <p>
            <div id="opt-time-info" style="float:left;mmargin-top:3px;">Loading...</div>
            <div id="opt-button-wrapper" style="float:right">
                <i id="opt-db-saving" class="wp-menu-image dashicons-before dashicons-cloud is-saving hide" style="vertical-align: bottom; margin-right: 10px; display: inline-flex;"></i>
                <button id="opt-dump" class="button button-primary" style="min-width: 150px;" data-type="mysql-dump" data-href="/api/mysql/mysql/dump" disabled="disabled">Jetzt sichern</button>
                <a id="opt-more" class="button" href="" target="_blank">Mehr</a>
            </div>
            <div style="clear:both;"></div>
        </p>
    </div>
<?php } ?>

<script type="text/x-jquery-tmpl" id="timeInfoTemplate">
    {{if human}}
        Letztes Backup vor <i class="timespan ${overdue(human)}">${human.total} ${human.name}</i><span> am ${created}</span>
    {{else}}
        Letztes Backup: <span style="color: #f00;"><strong>Noch kein Backup vorhanden!</strong></span>
    {{/if}}
</script>

<script type="text/x-jquery-tmpl" id="errorTemplate">
    <span>Error: {{if message}}${message}{{else}}An Error has occurred{{/if}}</span>
</script>