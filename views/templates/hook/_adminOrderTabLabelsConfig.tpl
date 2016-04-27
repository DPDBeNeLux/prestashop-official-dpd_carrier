<script>
    var DPDConfig = new function() {
        this.controllerUrl = "{html_entity_decode($controllerUrl|escape:'htmlall':'UTF-8')}";
        this.id_order = {$order->id|escape:'htmlall':'UTF-8'};
    }
</script>