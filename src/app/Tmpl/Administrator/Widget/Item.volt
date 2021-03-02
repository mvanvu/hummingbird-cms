<div class="widget-item" data-refresh="{{ willRefresh ? 1 : 0 }}">
    <form class="uk-form-horizontal" action="{{ action }}" method="post">
        {{ formsManager.renderFormFields('general') }}
        {{ formsManager.renderFormFields('params') }}
        {{ csrfInput() }}
    </form>
</div>