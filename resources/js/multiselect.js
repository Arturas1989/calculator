window.onload=function(){
    const DOM = document.querySelector(".multiple");
    DOM.addEventListener('mousedown',function(e){
        option = DOM.getElementsByTagName('option')[e.target.id]
        if(option){
            e.preventDefault();
            if(option.selected){
                option.selected ='';
            }
            else{
                option.selected = 'selected';
            }
            // console.log(this.selectedIndex);
            console.log(e.target.id);
            console.log(option.selected);
            // e.target.addEventListener('onFocus',function(e){

            // });
        };
    })
    DOM.addEventListener('click',function (){
        let result = "";
        for(let i = 0; i<DOM.length; ++i){
            let currentOption = DOM[i];
            if(currentOption.selected){
                result += currentOption.value + ' ';
            }
        }
        document.querySelector(".output").insertAdjacentHTML('beforeend',result);
    })
    
    
}
