{% set state = item.state %}

<div class="item-status uk-flex uk-flex-nowrap" data-entity-id="{{ item.id }}"
     data-uri="{{ uri.routeTo('status') }}">
    <a class="p{{ 'P' === state ? ' uk-disabled active' : '' }}" href="" uk-icon="icon: check" data-state="P"
       uk-tooltip="{{ _('publish-this-item') }}"></a>
    <a class="u{{ 'U' === state ? ' uk-disabled active' : '' }}" href="" uk-icon="icon: close"
       data-state="U" uk-tooltip="{{ _('disable-this-item') }}"></a>
    <a class="t{{ 'T' === state ? ' uk-disabled active' : '' }}" href="" uk-icon="icon: trash"
       data-state="T" uk-tooltip="{{ _('move-to-trash') }}"></a>
</div>
