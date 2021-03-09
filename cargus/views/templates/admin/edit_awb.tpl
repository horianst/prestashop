<script>
    $(function () {
        $('#content').removeClass('nobootstrap').addClass('bootstrap');
    });
</script>

<style>
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
    <form id="edit_form" class="form-horizontal" name="edit_form" method="post">
        <div class="panel">
            <div class="panel-heading"><i class="icon-align-justify"></i> Editare AWB comanda nr. todo</div>
            <br /><br />
            <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Expeditor</div>
            <div class="form-group">
                <input type="hidden" name="id_country" value="36" />

                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Punct de ridicare</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=pickup_id options=$pickups selected=$pickupsSelect class='filter center' style='width:200px; float:left;'}
                </div>
            </div>
            <br />
            <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Destinatar</div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Nume destinatar</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="name" value="{$data.name}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Judet</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=id_state options=$states selected=$statesSelect class='filter center' style='width:200px; float:left;'}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Localitate</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="city" value="{$data.locality_name}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Adresa de livrare</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="address">{$data.address}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Cod postal</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="postal_code">{$data.postal_code}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Persoana de contact</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="contact" value="{$data.contact}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Telefon</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="phone" value="{$data.phone}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Email</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="email" value="{$data.email}" />
                </div>
            </div>
            <br />
            <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Detalii AWB</div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Plicuri</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="envelopes" value="{$data.envelopes}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Colete</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="parcels" value="{$data.parcels}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Greutate</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="weight" value="{$data.weight}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Lungime</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="lenght" value="{$data.lenght}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Latime</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="width" value="{$data.width}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Inaltime</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="height" value="{$data.height}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Valoare declarata</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="value" value="{$data.value}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs numerar</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="cash_repayment" value="{$data.cash_repayment}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs cont colector</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="bank_repayment" value="{$data.bank_repayment}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs alt tip</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="other_repayment">{$data.other_repayment}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Platitor expeditie</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=payer options=$payers selected=$payerSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare dimineata</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=morning_delivery options=$yesNo selected=$yesNoDimineata}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare sambata</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=saturday_delivery options=$yesNo selected=$yesNoSambata}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Deschidere colet</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=openpackage options=$yesNo selected=$yesNoDeschidereColet}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Observatii</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="observations">{$data.observations}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Continut</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="contents">{$data.contents}</textarea>
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