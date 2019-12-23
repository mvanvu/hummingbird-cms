<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    <div id="admin-nested-list" data-token="{{ helper('Form::getToken') | escape_attr }}" data-base-uri="{{ uri.routeTo('') | escape_attr }}">
        {% if paginate.getTotalItems() %}
            <div class="uk-padding-small uk-background-muted">
                <div class="dd nestable">
                    {{ nestedHelper.makeTree() }}
                </div>
            </div>
            {{ partial('Pagination/Pagination') }}
        {% else %}
            <div class="uk-alert uk-alert-warning">
                {{ _('no-items-found') }}
            </div>
        {% endif %}
        <input type="hidden" name="postAction"/>
        <input type="hidden" name="entityId"/>
        {{ helper('Form::tokenInput') }}
    </div>
</form>