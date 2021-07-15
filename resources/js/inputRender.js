



function generateInputs(selector){

    const DOM = document.querySelector(selector);

    function handler(e){
        if(e.target && e.target.matches(".click")) {
            const targetName = e.target.name;
            const targetLength = e.target.name.length;
            const endNumText = targetName.substring(9,targetLength);
            let endNum = parseInt(endNumText);
            endNum++;
            e.target.classList.remove("click");
            const HTML = 
            `<div class="input-row">
                <div class="form-group form">
                    <label>Kodas</label>
                    <input type="text" class="form-control code" name="code-${endNum}" value="" required>
                </div>
                <div class="form-group form">
                    <label>Kiekis</label>
                    <input type="text" class="form-control click" name="quantity-${endNum}" value="" required>
                </div>
                <div class="form-group form">
                    <label>Data</label>
                    <input type="date" class="form-control" name="load_date-${endNum}" value="" required>
                </div>
                <div class="form-group form">
                    <label>Plotis</label><br>
                    <p id="width-${endNum}"></p>
                </div>
                <div class="form-group form">
                    <label>Ilgis</label><br>
                    <p id="length-${endNum}"></p>
                </div>
                <div class="form-group form">
                    <label>Gaminių iš ruošinio</label><br>
                    <p id="from_sheet_count-${endNum}"></p>
                </div>
            </div>`

            DOM.insertAdjacentHTML('beforeend',HTML);
        }
    }
    
    DOM.addEventListener('keydown',handler,false);
    DOM.addEventListener('click',handler,false); 
}

    function removeLastChild(selector){

        const DOM = document.querySelector(selector);

        DOM.addEventListener('click', function(e){

            const DOM2 = document.querySelector('.click');
            const childNodeList = DOM2.childNodes;
            const length = childNodeList.length;

            for (let i = length-1; i >= 0; i--) {
                if(childNodeList[i].classList){
                    
                    if(childNodeList[i].classList.contains('first')){
                        break;
                    }

                    DOM2.removeChild(childNodeList[i]);
                    break;
                }
            }

            const allDOM = document.querySelectorAll('.form-group>input');
            allDOM[allDOM.length-3].classList.add('click');
        });
    }
    
window.onload=function inputRender(){
    generateInputs(".click");
    removeLastChild('.close');
  }
  
