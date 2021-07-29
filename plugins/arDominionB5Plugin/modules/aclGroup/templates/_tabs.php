<nav>
  <ul class="nav nav-pills mb-2">
  <?php foreach ($groupsMenu->getChildren() as $child) { ?>
      <?php $options = ['class' => 'nav-link']; ?>
      <?php if (
          str_replace('%currentId%', $sf_request->id, $child->path)
          == $sf_context->getRouting()->getCurrentInternalUri()
      ) { ?>
        <?php $options['class'] .= ' active'; ?>
        <?php $options['aria-current'] = 'page'; ?>
      <?php } ?>
      <li class="nav-item">
        <?php echo link_to(
            $child->getLabel(['cultureFallback' => true]),
            $child->getPath(['getUrl' => true, 'resolveAlias' => true]),
            $options
        ); ?>
      </li>
    <?php } ?>
  </ul>
</nav>
