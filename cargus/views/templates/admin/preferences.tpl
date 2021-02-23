<script>
    $(function () {
        $('#content').removeClass('nobootstrap').addClass('bootstrap');
    });
</script>

<style>
    .icon-AdminUrgentCargus:before {
        content: "\f0d1";
    }

    label {
        width: 220px;
        font-weight: normal;
        padding: 0.4em 0.3em 0 0;
    }

    input[type="text"] {
        margin-bottom: 3px;
        width: 250px;
    }

    #edit_form span {
        color: #666;
        font-size: 11px;
        line-height: 0;
    }
</style>

<div class="entry-edit">
    <form id="edit_form" class="form-horizontal" name="edit_form" method="post"
          action="">
        <div class="panel">
            <div class="panel-heading"><i class="icon-align-justify"></i> Preferinte modul Cargus</div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Punctul de ridicare</span>
                </label>
                <div class="col-lg-10">
                    {html_options name=CARGUS_PUNCT_RIDICARE options=$pickups selected=$pickupsSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Asigurare expeditie</span>
                </label>
                <div class="col-lg-10">
                    {html_options name=CARGUS_ASIGURARE options=$yesNo selected=$yesNoAsiguare}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare sambata</span>
                </label>
                <div class="col-lg-10">
                    {html_options name=CARGUS_SAMBATA options=$yesNo selected=$yesNoSambata}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare dimineata</span>
                </label>
                <div class="col-lg-10">
                    {html_options name=CARGUS_DIMINEATA options=$yesNo selected=$yesNoDimineata}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Deschidere colet</span>
                </label>
                <div class="col-lg-10">
                    {html_options name=CARGUS_DESCHIDERE_COLET options=$yesNo selected=$yesNoDeschidereColet}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Tip ramburs</span>
                </label>
                <div class="col-lg-10">
                    {html_options name=CARGUS_TIP_RAMBURS options=$ramburs selected=$rambursSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Alegeti cine plateste costul serviciului de transport catre Cargus"
                          data-html="true">Platitor expeditie</span>
                </label>
                <div class="col-lg-10">
                    {html_options name=CARGUS_PLATITOR options=$platitor selected=$platitorSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Tip expeditie</span>
                </label>
                <div class="col-lg-10">
                    {html_options name=CARGUS_TIP_EXPEDITIE options=$expeditie selected=$expeditieSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Daca totalul cosului depaseste suma in lei introdusa, transportul va fi gratuit"
                          data-html="true">Limita transport gratuit</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" name="CARGUS_TRANSPORT_GRATUIT" value="{$transport}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Modulul nu va mai calcula dinamic costul transportului si va fi afisata suma in lei introdusa"
                          data-html="true">Cost fix transport</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" name="CARGUS_COST_FIX" value="{$cost}"/>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" name="submit" value="submit" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> Salveaza
                </button>
            </div>
        </div>
    </form>
</div>