{extends file='page.tpl'}

{block name='page_content_container'}
  <div id="custom-text">
    <h1>{l s='Oops...' mod='psemailvalidation'}</h1>
    <p>
      {l s='We ran into an issue while signing you in.' mod='psemailvalidation'}<br>
      <strong>{$error_message}</strong><br>
      {l s='Please, get in touch with support.' mod='psemailvalidation'}
    </p>
  </div>
{/block}
