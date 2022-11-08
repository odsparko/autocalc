function sendHeight (){
        var height = $('.container-fluid').outerHeight()
        var message = {}
        message['height'] = height+20
        window.parent.postMessage(message, "*")
    }
    
    function getData(submit) {
        var formCalc = $('#formCalc').serialize();

        $.get("calc.php?"+formCalc,
           function(data) {
           //console.log(data);
             $('#auction').html(data.auc_select);
             $('#age').val(data.age);
             $('#vol').val(data.vol);
             $('#power').val(data.power);
             $('#power_type').val(data.power_type);
             $('#price').val(data.price);
             $('#currency').val(data.currency);
             
             if(data.glonas != '0,00') {
                $('#glonas').prop( "checked", true);
             }
             else {
                $('#glonas').prop( "checked", false);
             }
             $('#tcurrent').html('');
             $('#tcurrent').append("1 &euro; = "+ data.currencies[1].rate +" &#8381;<br>");
             $('#tcurrent').append("1 $ = "+ data.currencies[2].rate +" &#8381;<br>");
             $('#tcurrent').append(data.currencies[4].nominal + "&#165; = "+ (data.currencies[4].rate) +" &#8381;<br>");

             
             if(submit) {
                 
                 var itog_vl = +data.price + +data.fob_text + +data.dosyavkaDoVlad;
                 
                 $('#result').append("<tr class='table-info'><td><b>Расходы по Японии</b></td><td></td><td></td></tr>");
                 $('#result').append("<tr class='table-info'><td>Стоимость авто</td><td><small>"+ data.currencies[data.currency].html+""+data.price+"</small></td><td>"+data.price_rub+"</td></tr>");
                 $('#result').append("<tr class='table-info'><td>FOB <small>("+data.auction_text+")</small></td><td><small>&#165;"+data.fob_text+"</small></td><td>"+data.fob+"</td></tr>");
                 $('#result').append("<tr class='table-info'><td>Доставка до Владивостока</td><td><small><nobr>&#165;"+data.dosyavkaDoVlad+"</nobr></small></td><td>"+data.dostavka+"</td></tr>");
                 $('#result').append("<tr class='table-info'><td><b>Итого C&F Владивосток</b></td><td></td><td><b>"+data.itog_vl_rub+"</b></td></tr>");
                 
                 $('#result').append("<tr class='table-warning'><td><b>Таможенные платежи</b></td><td></td><td></td></tr>");
                 $('#result').append("<tr class='table-warning'><td>Сбор</td><td></td><td>"+data.sbor+"</td></tr>");
                 $('#result').append("<tr class='table-warning'><td>Утилизационный сбор</td><td><small>"+data.us_text+"</small></td><td>"+data.us+"</td></tr>");
                 if(data.akciz!='0,00') $('#result').append("<tr class='table-warning'><td>Акциз</td><td><small>"+data.akciz_text+"</small></td><td>"+data.akciz+"</td></tr>");
                 $('#result').append("<tr class='table-warning'><td>Таможенная пошлина</td><td><small>"+data.tstavka_text+"</small></td><td>"+data.tstavka+"</td></tr>");
                 if(data.nds!='0,00') $('#result').append("<tr class='table-warning'><td>НДС</td><td><small>20%</small></td><td>"+data.nds+"</td></tr>");
                 $('#result').append("<tr class='table-warning'><td>Услуги по таможенному оформлению, СВХ, СБКТС</td><td></td><td>"+data.svh+"</td></tr>");
                 if(data.glonas!='0,00') $('#result').append("<tr class='table-warning'><td>Эра-Глонасс</td><td></td><td>"+data.glonas+"</td></tr>");
                 $('#result').append("<tr class='table-warning'><td><b>Итого таможенные платежи</b></td><td></td><td><b>"+data.titog+"</b></td></tr>");
                 
                 $('#result').append("<tr class='table-success'><td>Комиссия Storeautodv</td><td></td><td>"+data.service+"</td></tr>");
                 $('#result').append("<tr class='table-success'><td>Вывоз авто в лабораторию</td><td></td><td>"+data.laboratory+"</td></tr>");
                 $('#result').append("<t><td><b>Итого</b></td><td></td><td><b><nobr>"+data.itog+"</nobr></b></td></tr>");

                 $('#table_result').show();
                 
                 $('html, body').animate({
                    scrollTop: $("#calcbtn").offset().top
                }, 1000);
                setTimeout(sendHeight, 1) 
                 
             }
             
             
           }, "json");
    };
    function checkEngine() {
    
        if($('#engine').val() == 4) {
            $('#divengine').hide();
            setTimeout(sendHeight, 1)
        }
        else {
            $('#divengine').show();
            setTimeout(sendHeight, 1)
        }
    };
    checkEngine();
    getData(false);
    
    
    $('#engine').change(function(e) {
        checkEngine();
    });
    
    $("#formCalc").submit(function(e){
        e.preventDefault();
        $('#result').html('');
        getData(true);
    });
    
    $(document).ready(function() {
        sendHeight()
    })

    $(window).on('resize', sendHeight)