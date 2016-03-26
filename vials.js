Array.prototype.remove = function(from, to) {
	var rest = this.slice((to || from) + 1 || this.length);
	this.length = from < 0 ? this.length + from : from;
	return this.push.apply(this, rest);
};

Array.prototype.delete = function(obj) {
	var i = this.indexOf(obj);
	if (i == -1) { return } else { this.remove(i)};
}

Array.prototype.contains = function(obj) {
	return this.indexOf(obj) != -1;
}

// Relevant stuff starts here...

var vials = {
	"blood":	{'image': 'aspectbloodgel.png', 'inner': '#ba1016'},
	"breath":	{'image': 'aspectbreathgel.png', 'inner': '#10e0ff'},
	"doom":		{'image': 'aspectdoomgel.png', 'inner': '#292E2B'},
	"heart":	{'image': 'aspectheartgel.png', 'inner': '#bd1864'},
	"hope":		{'image': 'aspecthopegel.png', 'inner': '#FFF586'},
	"life":		{'image': 'aspectlifegel.png', 'inner': '#76c34e'},
	"light":	{'image': 'aspectlightgel.png', 'inner': '#f6fa4e'},
	"mind":		{'image': 'aspectmindgel.png', 'inner': '#06ffc9'},
	"rage":		{'image': 'aspectragegel.png', 'inner': '#9c4dad'},
	"space":	{'image': 'aspectspacegel.png', 'inner': '#ECE9E9'},
	"time":		{'image': 'aspecttimegel.png', 'inner': '#ff2106'},
	"void":		{'image': 'aspectvoidgel.png', 'inner': '#00164f'},
	"aqua":		{'image': 'healthaquagel.png', 'inner': '#0BAFFF'},
	"black":	{'image': 'healthblackgel.png', 'inner': '#000000'},
	"blue":		{'image': 'healthbluegel.png', 'inner': '#0021cb'},
	"fuchsia":	{'image': 'healthfuchsiagel.png', 'inner': '#99004d'},
	"gold":		{'image': 'healthyellowgel.png', 'inner': '#ffbb22'},
	"gray":		{'image': 'healthblackgel.png', 'inner': '#535353'},
	"green":	{'image': 'healthgreengel.png', 'inner': '#1f9400'},
	"grey":		{'image': 'healthblackgel.png', 'inner': '#b8b8b8'},
	"lime":		{'image': 'healthlimegel.png', 'inner': '#2cff4b'},
	"maroon":	{'image': 'healthmaroongel.png', 'inner': '#a10000'},
	"navy":		{'image': 'healthnavygel.png', 'inner': '#000056'},
	"olive":	{'image': 'healtholivegel.png', 'inner': '#416600'},
	"orange":	{'image': 'healthorangegel.png', 'inner': '#f2a400'},
	"pink":		{'image': 'healthfuchsiagel.png', 'inner': '#ff4d9d'},
	"purple":	{'image': 'healthpurplegel.png', 'inner': '#f092ff'},
	"red":		{'image': 'healthredgel.png', 'inner': '#ff0000'},
	"silver":	{'image': 'healthsilvergel.png', 'inner': '#eeeeee'},
	"teal":		{'image': 'healthtealgel.png', 'inner': '#008282'},
	"white":	{'image': 'healthwhitegel.png', 'inner': '#DDDDDD'},
	"yellow":	{'image': 'healthyellowgel.png', 'inner': '#ffff00'},
}

function drawVial(holder, type, value) {
	var offset = 100 - value <= 27 ? 0 : Math.abs(27-(100-value));
	var paper = Raphael(holder, 159 + offset, 33), set = paper.set();
	var base = set.push(paper.image("Images/vials/"+vials[type].image,offset,0, 159, 33));
	var vial = set.push(paper.image("Images/vials/vial.png",offset > 0 ? 0 : value-73,10, 116, 14));
	var health = set.push(paper.rect(offset + 35, 14, value, 6));
	health.attr({fill: vials[type].inner, stroke: "none"});
	paper.renderfix();
}

// ... and ends here.

function drawVials(size) {
	$("#health").html(""); 
	$("#aspect").html("");
	drawVial("health", $("#healthSelect option:selected").val(), size);
	drawVial("aspect", $("#aspectSelect option:selected").val(), size);
	$("#sliderText").html(size);
}

