function showPage(pageId){

    // Hide all sections
    document.querySelectorAll('.page').forEach(function(page){
        page.classList.remove('active');
    });

    // Show selected section
    document.getElementById(pageId).classList.add('active');
}