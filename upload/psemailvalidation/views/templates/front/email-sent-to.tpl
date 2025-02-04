{extends file='page.tpl'}

{block name='page_content_container'}
  <div id="custom-text">
    <h1>{l s='Email validation' mod='psemailvalidation'}</h1>
    <p>
      <br>
      {l s='A verification email has been sent to the inbox ' mod='psemailvalidation'}<br>
      <strong>{$customer_email}</strong>.
    </p>
    <p>
      {l s='It is possible that our message gets delivered to your spam folder.' mod='psemailvalidation'}
    </p>
  <div>
{/block}
