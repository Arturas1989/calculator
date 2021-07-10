function generateInputs(selector){

    const DOM = document.querySelector(selector);

    function handler(e){
        if(e.target && e.target.matches(".click")) {
            const targetName = e.target.name;
            const targetLength = e.target.name.length;
            const endNumText = targetName.substring(5,targetLength);
            let endNum = parseInt(endNumText);
            endNum++;
            e.target.classList.remove("click");
            const HTML = 

        
            `<div class="form-group form">
                <label>Kodas</label>
                <input type="text" class="form-control click" name="code-${endNum}" value="" required>
            </div>
            <div class="form-group form">
                <label>Kiekis</label>
                <input type="text" class="form-control" name="quantity-${endNum}" value="" required>
            </div>
            <div class="form-group form">
                <label>Data</label>
                <input type="date" class="form-control" name="date-${endNum}" value="" required>
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
            let count =0;

            for (let i = length-1; i >= 0; i--) {
                if(childNodeList[i].classList){
                    
                    if(childNodeList[i].classList.contains('first')){
                        break;
                    }

                    DOM2.removeChild(childNodeList[i]);
                    count++;

                    if(count===3){
                        break;
                    }
                }
            }

            const allDOM = document.querySelectorAll('.form-group>input');
            allDOM[allDOM.length-3].classList.add('click');
        });
    }

window.onload=function(){
    generateInputs(".click");
    removeLastChild('.close');
  }
  
