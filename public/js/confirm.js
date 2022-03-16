function deleteHandle(event) {
    event.preventDefault();
    if(window.confirm('Do you really want to delete?')){
        document.getElementById('delete-form').submit();
    }else{
        alert('Canceled');
    }
}

function tagDeleteHandle(event) {
    event.preventDefault();
    if(window.confirm('Do you really want to delete?')){
        document.getElementById('tag-delete-form').submit();
    }else{
        alert('Canceled');
    }
}
