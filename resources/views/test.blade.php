
<form method="post" action="https://ccore.newebpay.com/MPG/mpg_gateway">
    MID: <input name="MerchantID" value="{{ $mid }}" readonly><br>
    Version: <input name="Version" value="2.0" readonly><br>
    TradeInfo: <input name="TradeInfo" value="{{ $edata1 }}" readonly><br>
    TradeSha: <input name="TradeSha" value="{{ $hash }}" readonly><br>
    <input type="submit">
</form>

   