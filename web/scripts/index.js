$(document).ready(function(){
	if(compare===true){
		getValues(dataDir+"/NMT1", sentenceNum);
		addHighlight(sentenceNum);
		setTimeout(function(){
			other = true;
			getValues(dataDir+"/NMT2", sentenceNum);
		}, 500)
	}else{
		getValues(dataDir, sentenceNum);
		addHighlight(sentenceNum);
	}
})

var other = false;

function changeSentence(dataDirectory, sentenceNumber){
	$('#loadCont').show();
	
    getValues(dataDirectory, sentenceNumber);
	window.history.pushState('NMT Attention Alignments', 'NMT Attention Alignments', '?directory='+dataDirectory+'&s='+sentenceNumber);
	
	setTimeout(function(){
		$('#loadCont').hide();
	}, 200)
}

function removeHighlight(sentenceNumber){
    $('#translation-'+sentenceNumber).removeClass('highlighted')
    $('#confidence-'+sentenceNumber).removeClass('highlighted')
    $('#deviation-'+sentenceNumber).removeClass('highlighted')
    $('#apout-'+sentenceNumber).removeClass('highlighted')
    $('#apin-'+sentenceNumber).removeClass('highlighted')
}

function addHighlight(sentenceNumber){
    $('#translation-'+sentenceNumber).addClass('highlighted')
    $('#confidence-'+sentenceNumber).addClass('highlighted')
    $('#deviation-'+sentenceNumber).addClass('highlighted')
    $('#apout-'+sentenceNumber).addClass('highlighted')
    $('#apin-'+sentenceNumber).addClass('highlighted')
}

function jumpForm(){
    var jumpNum = $('#sentenceNum').val();
    if (jumpNum > 0 && jumpNum <= sentenceCount){
        jumpTo(dataDir, jumpNum);
    }
    return false;
}

function jumpTo(dataDirectory, sentenceNumber){
    removeHighlight(sentenceNum);
    sentenceNum = sentenceNumber;
    $('#sentenceNum').val(sentenceNum);
    changeSentence(dataDirectory, sentenceNum)
    addHighlight(sentenceNum);
}

function getNext(dataDirectory, sentenceNumber){
    if(sentenceCount >= sentenceNumber){
        removeHighlight(sentenceNum);
        if(sentenceCount > sentenceNumber){
            sentenceNum++;
            $('#sentenceNum').val(sentenceNum);
        }
        changeSentence(dataDirectory, sentenceNum)
        addHighlight(sentenceNum);
    }
}

function getPrev(dataDirectory, sentenceNumber){
    if(1 <= sentenceNumber){
        removeHighlight(sentenceNum);
        if(1 < sentenceNumber){
            sentenceNum--;
            $('#sentenceNum').val(sentenceNum);
        }
        changeSentence(dataDirectory, sentenceNum);
        addHighlight(sentenceNum);
    }
}
function processTop(content) {
    $("#topRow").html(content);
}
function processBottom(content) {
	if (other===false){
		$("#bottomRow").html(content);
	}else{
		$("#bottomRow2").html(content);
	}
}

function processData(ali_data) {
    sentenceCount = ali_data.count;
   
    hideShow(hide,show);
    if(hide == 'matrix'){
        $('#svgBut').button('toggle');
    }else{
		$("#other").hide();
        $('#matBut').button('toggle');
        render(ali_data.source,ali_data.target,ali_data.alignment_data);
        html2canvas($("#matrix"), {
            onrendered: function (canvas) {
				getCanvas = canvas;
            }
        });
    }
        
    var margin ={b:0, t:40, l:-10, r:0};
    
	if (other===false){
		d3.selectAll("#other > *").remove();
		var svg = d3.select("#other")
			.append("svg")
			.attr("preserveAspectRatio", "xMinYMin meet")
			.attr("viewBox", "0 0 620 "+(compare === true ? "265" : "240")+"")
			.attr("id", "ali")
			.classed("svg-content-responsive", true)
			.append("g")
			.attr("transform","translate("+ margin.l+","+margin.t+")");
		var data = [ 
			{data:bP.partData(ali_data.alignment_data,ali_data.source,ali_data.target), id:'SubWordAlignments'}
		];

		bP.draw(data, svg);
	}else{
		hideShow('matrix','svg');
		$("#other").show();
		var svg = d3.select("#svg")
			.append("svg")
			.attr("preserveAspectRatio", "xMinYMin meet")
			.attr("viewBox", "0 0 620 265")
			.attr("id", "ali")
			.classed("svg-content-responsive", true)
			.append("g")
			.attr("transform","translate("+ margin.l+","+margin.t+")");
		var data = [ 
			{data:bP.partData(ali_data.alignment_data,ali_data.source,ali_data.target), id:'SubWordAlignments'}
		];

		bP.draw(data, svg);
	}
    


    var getCanvas; // global variable

    d3.select("#save").on("click", function(){
        if(hide == 'matrix'){
            saveSvgAsPng(document.getElementById("ali"), "alignments_"+Date.now()+".png", {scale: 3, backgroundColor: '#FFFFFF'});
        }else{
            var imgageData = getCanvas.toDataURL("image/png");
            var newData = imgageData.replace(/^data:image\/png/, "data:application/octet-stream");
            $("#save").attr("download", "matrix_"+Date.now()+".png").attr("href", newData);
        }
    });

    $('#c1,#c2,#c3,#c4,#c5').perfectScrollbar({
      suppressScrollY: true,
      useBothWheelAxes: true
    });
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
        
        $('input[type=radio][name=type]').change(function() {
            if (this.value == 'svg') {
                hideShow('matrix','svg');
				$("#other").show();
            }
            else if (this.value == 'matrix') {
                render(ali_data.source,ali_data.target,ali_data.alignment_data);
                hideShow('svg','matrix');
				$("#other").hide();
                html2canvas($("#matrix"), {
                    onrendered: function (canvas) {
                            getCanvas = canvas;
                    }
                });
            }
        });
    });
   
}

function getValues(dataDirectory, sentenceNumber){
     $.ajax({
            'url': 'data.php?directory='+dataDirectory+'&s='+sentenceNumber,
            type: 'get',
            dataType: 'json',
            cache: false,
            success: processData,
            async:true,
    });
    //top
    $.ajax({
            'url': 'top.php?directory='+dataDirectory+'&s='+sentenceNumber,
            type: 'get',
            cache: false,
            success: processTop,
            async:true,
    });
    //bottom
    $.ajax({
            'url': 'bottom.php?directory='+dataDirectory+'&s='+sentenceNumber,
            type: 'get',
            cache: false,
            success: processBottom,
            async:true,
    });
};