<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class SearchAdvancedAction extends DefaultBrowseAction
{
  public static
    $NAMES = array(
      'copyrightStatus',
      'hasDigitalObject',
      'levelOfDescription',
      'materialType',
      'mediaType',
      'repository',
      'searchFields',
      'startDate',
      'endDate'
    );

  protected function addField($name)
  {
    switch ($name)
    {
      case 'copyrightStatus':
        $this->form->setValidator('copyrightStatus', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator('copyrightStatus', new sfValidatorString);
        $this->form->setWidget('copyrightStatus', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'hasDigitalObject':
        $choices = array(
          '' => '',
          'true' => $this->context->i18n->__('Yes'),
          'false' => $this->context->i18n->__('No')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'levelOfDescription':
        $this->form->setValidator('levelOfDescription', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator('levelOfDescription', new sfValidatorString);
        $this->form->setWidget('levelOfDescription', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'materialType':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MATERIAL_TYPE_ID);

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');
        $criteria->addAscendingOrderByColumn('name');

        $choices = array();
        $choices[null] = null;
        foreach (QubitTerm::get($criteria) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'mediaType':
        // Get list of media types
        $criteria = new Criteria;
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MEDIA_TYPE_ID);

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');
        $criteria->addAscendingOrderByColumn('name');

        $choices = array();
        $choices[null] = null;
        foreach (QubitTerm::get($criteria) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'repository':
        // Get list of repositories
        $criteria = new Criteria;

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor');

        $criteria->addAscendingOrderByColumn('authorized_form_of_name');

        $choices = array();
        $choices[null] = null;
        foreach (QubitRepository::get($criteria) as $repository)
        {
          $choices[$repository->id] = $repository->__toString();
        }

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;
    }
  }

  protected function processField($field)
  {
    if (null === $value = $this->form->getValue($field->getName()))
    {
      return;
    }

    $this->hasFilters = true;

    switch ($field->getName())
    {
      case 'copyrightStatus':
        $query = new \Elastica\Query\Term;
        $query->setTerm('copyrightStatusId', $value);
        $this->queryBool->addMust($query);

        break;

      case 'hasDigitalObject':
        $query = new \Elastica\Query\Term;
        $query->setTerm('hasDigitalObject', $value);
        $this->queryBool->addMust($query);

        break;

      case 'levelOfDescription':
        $query = new \Elastica\Query\Term;
        $query->setTerm('levelOfDescriptionId', $value);
        $this->queryBool->addMust($query);

        break;

      case 'materialType':
        $query = new \Elastica\Query\Term;
        $query->setTerm('materialTypeId', $value);
        $this->queryBool->addMust($query);

        break;

      case 'mediaType':
        $query = new \Elastica\Query\Term;
        $query->setTerm('digitalObject.mediaTypeId', $value);
        $this->queryBool->addMust($query);

        break;

      case 'repository':
        $query = new \Elastica\Query\Term;
        $query->setTerm('repository.id', $value);
        $this->queryBool->addMust($query);

        break;
    }
  }

  protected function parseQuery()
  {
    $queryBool = new \Elastica\Query\Bool();

    if (!isset($this->request->searchFields))
    {
      return;
    }

    $culture = $this->context->user->getCulture();

    // Iterate over search fields
    foreach ($this->request->searchFields as $key => $item)
    {
      if (empty($item['query']))
      {
        continue;
      }

      $queryText = new \Elastica\Query\Text();

      switch ($item['field'])
      {
        case 'identifier':
          $queryText->setFieldQuery('identifier', $item['query']);

          break;

        case 'title':
          $queryText->setFieldQuery('i18n.'.$culture.'.title', $item['query']);

          break;

        case 'scopeAndContent':
          $queryText->setFieldQuery('i18n.'.$culture.'.scopeAndContet', $item['query']);

          break;

        case 'archivalHistory':
          $queryText->setFieldQuery('i18n.'.$culture.'.archivalHistory', $item['query']);

          break;

        case 'extentAndMedium':
          $queryText->setFieldQuery('i18n.'.$culture.'.extentAndMedium', $item['query']);

          break;

        case 'creatorHistory':
          $queryText->setFieldQuery('', $item['query']);

          break;

        case 'subject':
          $queryText->setFieldQuery('', $item['query']);

          break;

        case 'name':
          $queryText->setFieldQuery('', $item['query']);

          break;

        case 'place':
          $queryText->setFieldQuery('', $item['query']);

          break;

        default:
          $queryText->setFieldQuery('_all', $item['query']);

          break;
      }

      if (0 == $key)
      {
        $item['operator'] == 'add';
      }

      switch ($item['operator'])
      {
        case 'not':
          $queryBool->addMustNot($queryText);

          break;

        case 'or':
          $queryBool->addShould($queryText);

          break;

        case 'add':
        default:
          $queryBool->addMust($queryText);

          break;
      }
    }

    return $queryBool;
  }

  public function execute($request)
  {
    parent::execute($request);

    if ('print' == $request->getGetParameter('media'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    // Stop if the input is not valid
    $this->form->bind($request->getRequestParameters() + $request->getGetParameters() + $request->getPostParameters());
    if (!$this->form->isValid())
    {
      die(" ERROR FORM ");
    }

    if (null !== $advancedSearchCriteriaQueryBool = $this->parseQuery())
    {
      $this->queryBool->addMust($advancedSearchCriteriaQueryBool);
    }
    else
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll());
    }

    // Process form fields
    foreach ($this->form as $field)
    {
      if (isset($this->request[$field->getName()]))
      {
        $this->processField($field);
      }
    }

    // Filter drafts
    $this->query = QubitAclSearch::filterDrafts($this->query);

    // Sort
    # $this->query->setSort(array($field => 'desc'));

    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
