{extends file='page.tpl'}

{block name='page_content'}
  <div id="custom-text">
    <h1>{l s='Successful verification' mod='psemailvalidation'}</h1>
    <p>
      <br>
      {l s='You will be redirected to ' mod='psemailvalidation'}
      <a href="{$redirectUrl|escape:"htmlall":"UTF-8"}">
        {l s='checkout' mod='psemailvalidation'}
      </a>
      {l s='in ' mod='psemailvalidation'}
      <strong id="countdown">{$delay/1000}</strong>
      .
    <p>
  </div>
  <script>
    var countdown = {$delay/1000};
    var countdownElement = document.getElementById("countdown");

    var timer = setInterval(function() {
      countdown--;
      countdownElement.textContent = countdown;

      if (countdown <= 0) {
        clearInterval(timer);
        window.location.href = '{$redirectUrl|escape:"htmlall":"UTF-8"}';
      }
    }, 1000);
  </script>
{/block}
