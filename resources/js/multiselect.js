


window.onload=function() 
{
    $('#boards').multiSelect
    ({
        selectableHeader: "<div class='custom-header'>Gofrų sąrašas</div>",
        selectionHeader: "<div class='custom-header'>Pasirinkta</div>",
    });

    $('#marks').multiSelect
    ({
        selectableHeader: "<div class='custom-header'>Markių sąrašas</div>",
        selectionHeader: "<div class='custom-header'>Pasirinkta</div>",
    });
    $('#marks_origin').multiSelect
    ({
        selectableHeader: "<div class='custom-header'>Markių sąrašas</div>",
        selectionHeader: "<div class='custom-header'>Pasirinkta</div>",
    });
    $('#marks_join').multiSelect
    ({
        selectableHeader: "<div class='custom-header'>Markių sąrašas</div>",
        selectionHeader: "<div class='custom-header'>Pasirinkta</div>",
    });
}



// window.onload=function(){
//     const DOM = document.querySelector(".multiple");
//     DOM.onscroll = function(e){
//         DOM.addEventListener('click', function (e){
//             if(e.target){
                
//                 const scrollTop = e.pageY;
//                 const scrollLeft = e.pageX;
//                 DOM.scrollTo(scrollLeft, scrollTop);
//             }
            
//         })
        
//     }
//     DOM.addEventListener('mousedown',function(e){
        
//         option = DOM.getElementsByTagName('option')[e.target.id]
//         if(option){
//             e.preventDefault();
//             if(option.selected){
//                 option.selected ='';
//             }
//             else{
//                 option.selected = 'selected';
//                 e.preventDefault();
                
                
//             }
            
//             // console.log(this.selectedIndex);
            
//             // e.target.addEventListener('onFocus',function(e){

//             // });
//         };
        
//     })
//     DOM.addEventListener('click',function (){
//         let result = "";
//         for(let i = 0; i<DOM.length; ++i){
//             let currentOption = DOM[i];
//             if(currentOption.selected){
//                 result += currentOption.value + ' ';
//             }
//         }
//         document.querySelector(".output").insertAdjacentHTML('beforeend',result);
//     })
    
    
// }
