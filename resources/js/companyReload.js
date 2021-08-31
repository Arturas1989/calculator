
const script = document.querySelector('#script');
const getCompany = script.dataset.getcompany;

window.onload=function(){
    DOM = document.querySelector(".click");
    DOM2 = document.querySelectorAll(".reload");
    DOM.addEventListener('click', function(){
        axios.get(getCompany)
                .then(response =>
                {
                    const data = response.data;
                    const length = data.length;
                    let HTML = '<option value="">Pasirinkite klientą</option>';
                    for (let i=0; i<length; ++i) {
                        HTML += `<option value="${data[i].id}">${data[i].company_name}</option>`;
                    }

                    for (element of DOM2) {
                        element.innerHTML = HTML;
                        console.log(element);
                    }
                   
                });
    })
    
}