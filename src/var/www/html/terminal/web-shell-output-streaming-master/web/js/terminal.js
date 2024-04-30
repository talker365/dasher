document.getElementById('start-button').addEventListener('click', function(event){
    var template = document.getElementById('iframe-template');
    var template = document.importNode(template.content, true);
    document.getElementById('output-container').appendChild(template);
});

document.getElementById('stop-button').addEventListener('click', function(event){
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'terminal.php', true);
    xhr.send('status=end');
});

document.getElementById('destroy-button').addEventListener('click', function(event){
    document.getElementById('output-container').innerHTML = '';
});