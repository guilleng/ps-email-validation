{extends file='page.tpl'}

{block name='page_content_container'}
  <div id="custom-text">
    <h1>{l s='We need to validate your e-mail' mod='psemailvalidation'}</h1>
    <p>
      {l s='A verification link has been sent to the inbox ' mod='psemailvalidation'}<br>
      <strong>{$customer_email}</strong><br>
      {l s='It is possible that our message gets delivered to your spam folder.' mod='psemailvalidation'}
    </p>
  <div>
{/block}
