{* styling fix - centering score *}
<style>
  .sv-vbadge-vb2fx-value {
    text-align: center
  }
</style>

{$importString = $oPlugin_admorris_pro->getPaths()->getFrontendURL()|cat:"js/shopVote.js"}

<script {$admShopVote.scriptTag} {if !$admShopVote.loadScript}data-{/if}src='{$admShopVote.scriptUrl}' defer></script>

<script data-name="admorris-shopvote" {if $admShopVote.loadScript}type="module"{else}type="text/plain" data-type="module"{/if}>
  import admHandleShopVote from '{$importString}'
  admHandleShopVote({json_encode($admShopVote)});
</script>