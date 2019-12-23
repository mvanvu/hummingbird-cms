{% set params = [
    'displayLayout': 'BlogList',
    'categoryIds': [postCategory.id | intval],
    'orderBy': 'latest',
    'postsNum': 5
] %}

<section class="uk-section uk-section-small">
    <div class="uk-container">
        <div class="uk-text-center">
            {{ partial('Breadcrumb/Breadcrumb') }}
            <h1 class="uk-margin-small-top uk-margin-remove-bottom">
                {{ postCategory.t('title') }}
            </h1>
        </div>
        {{ helper('Widget::createWidget', 'FlashNews', params, true, 'Raw') }}
    </div>
</section>