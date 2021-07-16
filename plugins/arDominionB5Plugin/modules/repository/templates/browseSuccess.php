<?php decorate_with('layout_2col'); ?>
<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex align-items-center mb-3">
    <i class="fas fa-3x fa-university me-3" aria-hidden="true"></i>
    <div class="d-flex flex-column">
      <h1 class="mb-0" aria-describedby="results-label">
        <?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?>
      </h1>
      <span class="small" id="results-label"><?php echo sfConfig::get('app_ui_label_repository'); ?></span>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('sidebar'); ?>

  <h2 class="d-grid">
    <button class="btn btn-lg atom-btn-white text-wrap mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-aggregations" aria-expanded="true" aria-controls="collapse-aggregations">
      <?php echo sfConfig::get('app_ui_label_facetstitle'); ?>
    </button>
  </h2>

  <div class="collapse show" id="collapse-aggregations">

    <?php echo get_partial('search/aggregation', [
        'id' => '#facet-languages',
        'label' => __('Language'),
        'name' => 'languages',
        'aggs' => $aggs,
        'filters' => $search->filters, ]); ?>

    <?php echo get_partial('search/aggregation', [
        'id' => '#facet-archivetype',
        'label' => __('Archive type'),
        'name' => 'types',
        'aggs' => $aggs,
        'filters' => $search->filters, ]); ?>

    <?php echo get_partial('search/aggregation', [
        'id' => '#facet-province',
        'label' => __('Geographic Region'),
        'name' => 'regions',
        'aggs' => $aggs,
        'filters' => $search->filters, ]); ?>

    <?php echo get_partial('search/aggregation', [
        'id' => '#facet-geographicsubregion',
        'label' => __('Geographic Subregion'),
        'name' => 'geographicSubregions',
        'aggs' => $aggs,
        'filters' => $search->filters, ]); ?>

    <?php echo get_partial('search/aggregation', [
        'id' => '#facet-locality',
        'label' => __('Locality'),
        'name' => 'locality',
        'aggs' => $aggs,
        'filters' => $search->filters, ]); ?>

    <?php echo get_partial('search/aggregation', [
        'id' => '#facet-thematicarea',
        'label' => __('Thematic Area'),
        'name' => 'thematicAreas',
        'aggs' => $aggs,
        'filters' => $search->filters, ]); ?>

  </div>

<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <section class="browse-options">
    <div class="row">
      <div class="span4">
        <?php echo get_component('search', 'inlineSearch', [
            'label' => __('Search %1%', ['%1%' => strtolower(sfConfig::get('app_ui_label_repository'))]),
            'landmarkLabel' => __(sfConfig::get('app_ui_label_repository')), ]); ?>
      </div>

      <div class="accordion mb-3" role="search">
        <div class="accordion-item">
          <h2 class="accordion-header" id="heading-adv-search">
            <button class="accordion-button<?php echo $show ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-adv-search" aria-expanded="<?php echo $show ? 'true' : 'false'; ?>" aria-controls="collapse-adv-search">
              <?php echo __('Advanced search options'); ?>
            </button>
          </h2>
          <div id="collapse-adv-search" class="accordion-collapse collapse<?php echo $show ? ' show' : ''; ?>" aria-labelledby="heading-adv-search">
            <div class="accordion-body">
              <?php echo get_component('repository', 'advancedFilters', [
                  'thematicAreas' => $thematicAreas,
                  'repositories' => $repositories,
                  'repositoryTypes' => $repositoryTypes, ] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?>
          </div>
        </div>
      </div>

      <?php echo get_partial('default/viewPicker', ['view' => $view, 'cardView' => $cardView,
          'tableView' => $tableView, 'module' => 'repository', ]); ?>

      <div class="pickers">
        <?php echo get_partial('default/sortPickers',
          [
              'options' => [
                  'lastUpdated' => __('Date modified'),
                  'alphabetic' => __('Name'),
                  'identifier' => __('Identifier'), ], ]); ?>
      </div>
    </div>
  </section>

<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php if ($view === $tableView) { ?>
    <?php echo get_partial('repository/browseTableView', ['pager' => $pager, 'selectedCulture' => $selectedCulture]); ?>
  <?php } elseif ($view === $cardView) { ?>
    <?php echo get_partial('repository/browseCardView', ['pager' => $pager, 'selectedCulture' => $selectedCulture]); ?>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
