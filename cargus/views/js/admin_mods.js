$(document).ready(function(){
    // adauga buton submit in head-ul orders
    $('.dropdown-menu, .dropdown-menu-right').prepend('<button type="button" id="add_cargus_bulk" class="dropdown-item js-common_refresh_list-grid-action add_cargus_bulk"><i class="material-icons">library_add</i>Adauga in lista de livrari Cargus</button>');

    // ruleaza ajax in loop pt adaugarea comenzilor selectate in lista de livrare
    $('.add_cargus_bulk').click( function () {
        if ($('[name="order_orders_bulk[]"]:checked').length == 0) {
            alert('Va rugam sa selectati cel putin o comanda!');
        } else {
            var add = 0;
            var err = 0;
            $('[name="order_orders_bulk[]"]:checked').each(function () {
                var id = parseInt($(this).val());
                $.ajax({
                    async: false,
                    url: cargus_url + cargus_admindir + '/index.php?controller=CargusAdmin&token=' + cragus_token +'&type=ADDORDER&secret=' + secret + '&id=' + id + '&rand=' + Math.floor((Math.random() * 1000000) + 1),
                    success: function (data) {
                        if (data == 'ok') {
                            ++add;
                        } else {
                            ++err;
                        }
                    }
                });
            });
            if (add > 0) {
                alert(add + ' comenzi au fost adaugate in expeditia curenta Cargus!');
            }
            if (err > 0) {
                alert(err + ' comenzi nu au putut fi adaugate in expeditia curenta Cargus!');
            }
        }
        return false;
    });
});