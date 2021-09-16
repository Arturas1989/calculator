const script = document.querySelector('#script');
const getMarks = script.dataset.getmarks;

window.addEventListener('load',function(event){
    const DOM = document.querySelector('#ms-boards');

    DOM.addEventListener('click',function(e){
    axios.get(getMarks)
        .then(response =>
        {
            const optionSelectors = document.querySelectorAll('#board');
            // console.log(optionSelectors[0].innerText)
            let selected = []
            for(boardOption of optionSelectors)
            {
                if(boardOption.selected) selected.push(boardOption.innerText);
            }
            const data = response.data;
            // console.log(data.BE)
            let HTML = '';
            let HTML2 = '';
            for (board of selected)
            {
                if(data[board])
                {
                    for(mark of data[board])
                    {
                        HTML+=`<option value="${mark.mark_id}">${mark.mark_name}</option>`
                        HTML2+=`<li id="49-selectable" class="ms-elem-selectable"><span>${mark.mark_name}</span></li>`
                    }
                }   
            }

            const marksOriginSelect = document.querySelector('#marks_origin');
            const marksOriginSelect2 = document.querySelector('#ms-marks_origin>div>ul');
            marksOriginSelect.innerHTML = HTML;
            const marksJoinSelect = document.querySelector('#marks_join');
            marksJoinSelect.innerHTML = HTML;
            marksOriginSelect2.innerHTML = HTML2;
            // <div class="ms-container" id="ms-boards"><div class="ms-selectable"><div class="custom-header">Gofrų sąrašas</div><ul class="ms-list" tabindex="-1"><li id="49-selectable" class="ms-elem-selectable"><span>B</span></li><li id="53-selectable" class="ms-elem-selectable"><span>BC</span></li><li id="52-selectable" class="ms-elem-selectable ms-hover" style=""><span>BE</span></li><li id="50-selectable" class="ms-elem-selectable"><span>C</span></li><li id="51-selectable" class="ms-elem-selectable"><span>E</span></li></ul></div><div class="ms-selection"><div class="custom-header">Pasirinkta</div><ul class="ms-list" tabindex="-1"><li id="49-selection" class="ms-elem-selection" style="display: none;"><span>B</span></li><li id="53-selection" class="ms-elem-selection" style="display: none;"><span>BC</span></li><li id="52-selection" class="ms-elem-selection ms-hover" style="display: none;"><span>BE</span></li><li id="50-selection" class="ms-elem-selection" style="display: none;"><span>C</span></li><li id="51-selection" class="ms-elem-selection"><span>E</span></li></ul></div></div>
            
        });
        
        
    })
})
// window.addEventListener('click',function(e){
//         console.log(e.target)
// })
