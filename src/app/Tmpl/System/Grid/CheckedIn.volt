{% set author = helper('User::getInstance', item.checkedBy).name %}
{% set date = helper('Date::relative', item.checkedAt) %}

<div class="uk-text-warning uk-display-inline-block uk-margin-small-right"
	 title="{{ _('checked-in-item-tip', ['author': author, 'date': date]) | escape_attr }}" uk-tooltip>
	{{ helper('IconSvg::render', 'lock') ~ ' ' ~ title }}
</div>