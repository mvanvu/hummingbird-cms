{% set paginate = paginator.paginate() %}
<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-small uk-table-hover uk-table-middle uk-table-divider">
            <thead>
            <tr>
                <th class="uk-table-shrink uk-text-nowrap">
                    <input class="uk-checkbox check-all" type="checkbox"/>
                </th>
                <th class="uk-width-expand">
                    {{ partial('Grid/Sort', ['text': _('title'), 'column': 'title', 'activeOrder' : activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap uk-visible@m">
                    {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder' : activeOrder]) }}
                </th>
                <th class="uk-text-nowrap uk-visible@m">
                    {{ partial('Grid/Sort', ['text': _('ID'), 'column': 'id', 'activeOrder' : activeOrder]) }}
                </th>
            </tr>
            </thead>
            <tbody class="sort-container">
            {% for item in paginate.items %}
                <tr>
                    <td>
                        <input class="uk-checkbox" type="checkbox" name="cid[]" value="{{ item.id }}"/>
                    </td>
                    <td class="uk-text-nowrap">
                        {% if item.isCheckedIn() %}
                            {{ partial('Grid/CheckedIn', ['item': item, 'title': item.title]) }}
                        {% else %}
                            <a class="uk-link-reset" href="{{ uri.routeTo('edit/' ~ item.id) }}">
                                {{ item.title | escape }}
                            </a>
                        {% endif %}

                        {% if item.description is not empty %}
                            <span class="uk-text-meta">
                                {{ helper('StringHelper::truncate', item.description | escape) }}
                            </span>
                        {% endif %}
                    </td>

                    <td class="uk-text-nowrap uk-visible@m">
                        {{ helper('Date::relative', item.createdAt) }}
                    </td>
                    <td class="uk-text-nowrap uk-visible@m">
                        {{ item.id }}
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
    {{ partial('Pagination/Pagination') }}
    {{ helper('Form::tokenInput') }}
</form>
