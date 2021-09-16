
const script = document.querySelector('#script');
const getCompany = script.dataset.getcompany;

window.onload=function(){
    rowContainer = document.querySelector(".card-body");
    companySelects = document.querySelectorAll(".reload");
    rowContainer.addEventListener('click', function(e){
        if(e.target && e.target.matches(".click")){
            const rowNum = parseInt(e.target.classList[3]);
            axios.get(getCompany)
                .then(response =>
                {
                    const data = response.data;
                    const length = data.length;
                    let HTML = '<option value="">Pasirinkite klientą</option>';
                    for (let i=0; i<length; ++i) {
                        HTML += `<option value="${data[i].id}">${data[i].company_name}</option>`;
                    }
                    HTML += `<div class="btn btn-primary click ${rowNum}">Perkrauti</div>`;
                    companySelects[rowNum].innerHTML = HTML;
                });
        }
        
    })
    
}