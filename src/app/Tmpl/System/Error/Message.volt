<div class="card"{{ exception ? ' style="max-width: 90%"' : '' }}>
    {% if exception %}
        <p>{{ '<span style="font-size: 24px">' ~ exception.getMessage() ~ '</span><br/>File: <strong><small>' ~ exception.getFile() ~ '</small></strong> at line: ' ~ exception.getLine() }}</p>
        <code>{{ implode('<br/>', preg_split('/\r\n|\n/', exception.getTraceAsString())) }}</code>
    {% else %}
        <h1>{{ title }}</h1>
        <p>{{ message }}</p>
    {% endif %}
    <a href="{{ route('/') }}">
        {{ _('back-to-homepage') }}
    </a>
</div>
