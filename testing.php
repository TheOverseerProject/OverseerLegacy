<style>
.shpo {
  display: none;
}

.gatcha {
  font-family: Courier;
  font-size: 16px;
  font-weight: 900;
  display: block;
  background-color: #EEEEEE;
  width: 300px;
  border-style: solid;
  border-width: 2px;
  padding-left: 10px;
  padding-right: 10px;
  padding-top: 2px;
  padding-bottom: 1px;
  margin-bottom: 2px;
  vertical-align: center;
  cursor: pointer;
}

input[type="radio"]:checked+label{
  color: #00AA00;
  border-width: 2px;
  border-color: #00AA00;
}

.raster {
  display: inline-block;
  background: url('raster.png');
  height: 22px;
  width: 19px;
}

.descripa {
  font-size: 13px;
}

input[type="radio"]:checked+label .raster{
  background: url('rasterg.png');
}

.ripinit {
  font-size: 20px;
  position: relative;
  top: -3px;
}

</style>

<img style="display: none;" src="rasterg.png">
<form method="post">

<label class="conshop">
    <input type="radio" name="shpo" id="shpo1" class="shpo" value="craz">
    <label class="gatcha">
        <span class="ripin">Pan Galactic Gargle Blaster</span>
        <div class="descripa">This item doesn't look like it can be equipped.</div>
        <div class="raster"></div>
        <span class="ripinit">3363695</span>
    </label>
</label>

<label class="conshop">
    <input type="radio" name="shpo" id="shpo2" class="shpo" value="daz">
    <label class="gatcha">
        <span class="ripin">Cosbytop Computer</span>
        <div class="descripa">You think you can use this to communicate with your friends.</div>
        <div class="raster"></div>
        <span class="ripinit">2010</span>
    </label>
</label>

<label class="conshop">
    <input type="radio" name="shpo" id="shpo3" class="shpo" value="nap">
    <label class="gatcha">
        <span class="ripin">Gravity Beat</span>
        <div class="descripa">You can't wield this weapon.</div>
        <div class="raster"></div>
        <span class="ripinit">26532000</span>
    </label>
</label>

<label class="conshop">
    <input type="radio" name="shpo" id="shpo1" class="shpo" value="happ">
    <label class="gatcha">
        <span class="ripin">Grimoire for Summoning the Zoologically Fictional</span>
        <div class="descripa">You can't wield this weapon.</div>
        <div class="raster"></div>
        <span class="ripinit">14472000</span>
    </label>
</label>
</form>