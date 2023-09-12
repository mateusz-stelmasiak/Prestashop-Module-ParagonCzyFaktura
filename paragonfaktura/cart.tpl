{*
Scripts for dynamically toggling required fields
*}

<script type="application/javascript">
    function turnOnRequiredData()
    {
        let requiredData = document.getElementById("requiredData");
        requiredData.style.display="flex";
    }

    function turnOffRequiredData()
    {
        let requiredData = document.getElementById("requiredData");
        requiredData.style.display="none";
    }

    function saved(){
        //show saved msg
        let savedDOM = document.getElementById("saved");
        savedDOM.style.display="block";

        //clear all input fields


        //hide required data after 1second
        setTimeout(
            function() {
                turnOffRequiredData();
                savedDOM.style.display="none";
        },
        500);

    }



</script>


{*
Form dynamically sends data to db with ajax.php
on each html input click
*}

<form action="#" method="POST" id="invoice_bill">
    <input type="hidden" value="{$id_cart}" name="invoice_bill_id" id="invoice_bill_id"/>
    <input type="hidden" value="{$id_address}" name="id_address" id="id_address"/>


    <h5>{l s='Select type of document' mod='paragonfaktura'}</h5>

    <div class="methodSelection">
        <label>
            <input onclick="turnOffRequiredData()" type="radio"  value="2" name="invoice_bill" {if $type == 2} checked {/if}/>
            {l s='Bill' mod='paragonfaktura'}
        </label>

        <label>
            <input onclick="turnOnRequiredData()" type="radio" value="1" name="invoice_bill" {if $type == 1} checked {/if}/>
            {l s='Invoice' mod='paragonfaktura'}
        </label>
    </div>


 {if !$company || !$nip}
    <div id="requiredData" class="requiredData" {if (($nip && $company) || $type == 2)} style="display:none" {/if}>
        <h5>{l s='Uzupełnij dane' mod='paragonfaktura'}</h5>


        {if !$nip }
             <div class="form-group">
                <label class="form-control-label required" for="nip">NIP</label>
                <input class="form-control" id="nip" type="text" value="" name="updateBusinessData" required/>
             </div>
          {/if}

         {if !$company }
             <div class="form-group">
                <label class="form-control-label required" for="companyName">Nazwa firmy</label>
                <input class="form-control" id = "companyName" type="text" value="" name="updateBusinessData" required/>
             </div>
        {/if}

        <div class="submition">
            <input class="btn btn-primary" type="button" value="Zapisz" onclick="saved()"/>
            <span style="display:none" id="saved">Zapisano!</span>
        </div>
    </div>
{/if}

</form>
