{extends file='page.tpl'}

{block name='page_content_container'}
  <div id="custom-text">
    <h1>{l s='Oops...' mod='psemailvalidation'}</h1>
    <p>
      <br>
      {l s='There was a problem activating your account.' mod='psemailvalidation'}<br><br>
      {l s='If you are sure that you used the link we sent you, get in touch with support.' mod='psemailvalidation'}
    </p>
  </div>
{/block}
