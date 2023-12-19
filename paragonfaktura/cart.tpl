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
            <input onclick="turnOffRequiredData()" type="radio" value="2"
                   name="invoice_bill" {if $type == 2} checked {/if}/>
            {l s='Bill' mod='paragonfaktura'}
        </label>

        <label>
            <input onclick="turnOnRequiredData()" type="radio" value="1"
                   name="invoice_bill" {if $type == 1} checked {/if}/>
            {l s='Invoice' mod='paragonfaktura'}
        </label>
    </div>

    {if (!$company || !$nip)}
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
                    <input class="form-control" id="companyName" type="text" value="" name="updateBusinessData"
                           required/>
                </div>
            {/if}

            <div class="submition">
                <input class="btn btn-primary" type="submit" value="Zapisz"/>
                <span id="feedback"></span>
            </div>
        </div>
    {/if}
</form>


{*
Scripts for dynamically toggling required fields
*}
<script type="application/javascript">
    function turnOnRequiredData() {
        let requiredData = document.getElementById("requiredData");
        requiredData.style.display = "flex";
    }

    function turnOffRequiredData() {
        let requiredData = document.getElementById("requiredData");
        requiredData.style.display = "none";
    }

    //validate NIP
    document.getElementById("nip")?.addEventListener("change", event => {
        let nipDOM = document.getElementById("nip");
        let nipValue = "" + nipDOM.value; //convert to string

        if (!isValidNip(nipValue)) {
            nipDOM.setCustomValidity("Niepoprawny NIP");
        } else {
            nipDOM.setCustomValidity("");
        }
        nipDOM.reportValidity();
    })

    //handle submition of additional needed data
    document.getElementById("invoice_bill").addEventListener('submit', event => {
        event.preventDefault();
        //reset feedback field
        let feedbackDOM = document.getElementById("feedback");
        feedbackDOM.innerText = "";
        feedbackDOM.innerText = "Zapisano!"   //show saved msg
        //hide feedback after 1second
        setTimeout(
            function () {
                feedbackDOM.innerText = "";
            },
            1000);
    });

    function isValidNip(nip) {
        if (typeof nip !== 'string') {
            return false;
        }

        if (nip.length !== 10) {
            return false;
        }

        nip = nip.replace(/[\ \-]/gi, '');

        const weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        let sum = 0;
        let controlNumber = parseInt(nip.charAt(9));
        let weightCount = weights.length;
        for (let i = 0; i < weightCount; i++) {
            sum += (parseInt(nip.charAt(i)) * weights[i]);
        }

        return sum % 11 === controlNumber;
    }
</script>
