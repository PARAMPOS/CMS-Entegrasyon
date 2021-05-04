<?php

Class EticConfig
{

	public static $order_themes = array(
		'pro' => 'PRO!tema (Önerilir)',
		// '3d' => 'Üç boyutlu JS tema (Seksi)',
		// 'cr' => 'Kredity Form (Resmi)',
		// 'st' => 'Basit standart form '
	);
	public static $installment_themes = array(
		'color' => 'Renkli (Önerilir)',
		'simple' => 'Basit (Renksiz)',
		'white' => 'Beyaz (Resmi)',
		'colorize' => 'Colorize (Seksi) '
	);
	public static $families = array(
		'axess', 'bonus', 'maximum', 'cardfinans', 'world', 'paraf', 'advantage', 'combo', 'miles-smiles'
	);
	public static $messages = array();
	public static $gateways;

	public static function get($key)
	{
		return get_option($key);
	}

	public static function set($key, $value)
	{
		return update_option($key, $value);
	}

	public static function getAdminGeneralSettingsForm($module_dir)
	{
		$t = '<form action="" method="post" id="general_settings_form" >
			<div>
				<div class="row ">
					<div style="display: inline-block;"> <!-- required for floating -->
					<p style="padding: 20px; font-size: 15px;">Modülün görünümü ve temel fonksyionlarını bu panelden değiştirebilirsiniz.</p>
				</div>
				<div style="display:inline; float: right; margin-right: 15px; margin-top:25px;"> 
					<button style="display: inline;" onclick="javascript:window.open(`https://sanalpospro.com/wordpress/`, `_blank`);" class="spp-btn spp-red-btn" href="#help" role="tab" data-toggle="tab"><i class="process-icon-help"></i> Yardım</button> 
					<button style="display: inline;" type="submit" name="submitspr" class="spp-btn spp-blue-btn"><i class="process-icon-save"></i> Ayarları Kaydet</button>
				</div>
			</div>';

		$t .= '
			<h1>Genel Ayarlar</h1>
			<div class="tabpanel show">';
// Enable Disable
		$woo_settings = get_option("woocommerce_sanalpospro_settings");

		$t .= '
		<div class="spp-box">
			<div>
		  <p class="spp-box-title">Eklenti Durumu</p>
		</div>
		 <div class="spp-box-option">
		  <select name="WOO_POSPRO_SETTINGS[enabled] id="">
			<option value="yes">Aktif</option>
			<option value="no" ' . ($woo_settings['enabled'] == 'no' ? 'SELECTED ' : '') . '> Pasif </option>
		  </select>
		</div>
		<h2 class="spp-box-desc">Eklentiyi aktifleştir.</h2>
		<button data-tooltip="Eklentiyi aktifleştir.Eklentiyi aktifleştir.Eklentiyi akt" class="spp-btn spp-blue-btn">
			Açıklama<br>
		</button>
		</div>';

// Hata Kayıt Modu
		$t .= '
		<div class="spp-box">
			<div>
		  <p class="spp-box-title">Detaylı İşlem Kaydı</p>
		</div>
		 <div class="spp-box-option" name="spr_config[POSPRO_DEBUG_MOD]">
		  <select name="WOO_POSPRO" id="">
			<option value="on" ' . (EticConfig::get("POSPRO_DEBUG_MOD") == 'on' ? 'SELECTED ' : '') . '> Açık </option>
			<option value="off" ' . (EticConfig::get("POSPRO_DEBUG_MOD") == 'off' ? 'SELECTED ' : '') . '> Kapalı </option>
		  </select>
		</div>
		<h2 class="spp-box-desc">Tüm işlemleri incelemek üzere kaydeder.</h2>
		<button class="spp-btn spp-blue-btn">Açıklama</button>
		</div>';

//3D Auto Form
		$t .= '

		<div class="spp-box">
			<div>
		  <p class="spp-box-title">3DS Otomatik Yönlendirme</p>
		</div>
		 <div class="spp-box-option">
		  <select name="spr_config[POSPRO_ORDER_AUTOFORM]">
			<option value="on" > Açık (önerilir)</option>
			<option value="off" ' . (EticConfig::get("POSPRO_ORDER_AUTOFORM") == 'off' ? 'SELECTED ' : '') . ' > Kapalı </option>
		  </select>
		</div>
		<h2 class="spp-box-desc">3DS formlarını otomatik yönlendir.</h2>
		<button class="spp-btn spp-blue-btn">Açıklama</button>
		</div>';
// currency

		$t .= '
		<div class="spp-box">
			<div>
		  <p class="spp-box-title">Otomatik TL çevirimi</p>
		</div>
		 <div class="spp-box-option">
		  <select name="spr_config[POSPRO_AUTO_CURRENCY]">
			<option value="on"> Açık (önerilir)</option>
			<option value="off" ' . (EticConfig::get("POSPRO_AUTO_CURRENCY") == 'off' ? 'SELECTED ' : '') . '> Kapalı </option>
		  </select>
		</div>
		<h2 class="spp-box-desc">Yabancı kurlar ödemeleri TL ye çevirir.</h2>
		<button class="spp-btn spp-blue-btn">Açıklama</button>
		</div>';


		$t .= '</div>'; //row
// Açıklama
		/*  $t .= '<div class="col-md-6 text-center sppbox bgred">
		  <h2>Önemli</h2>
		  Posların çoğu USD ve EUR codelarındaki ödemeleri kabul eder ve EticSoft SanalPos PRO! tüm kurları destekler.
		  Fakat sanal POS hizmetinizin banka tanımlarında eksiklik ve hatalar olması gibi durumlarda hatalı veya başarısız ödemelerle karşılaşabilirsiniz.<br/>
		  <b>Para birimlerinin ISO ve Numerik codelarını doğru girmeniz oldukça önemlidir. Örneğin Türk lisasının ISO codeu "TRY" dir.</b>
		  <br/>
		  <br/>
		  </div>
		  </div>';
		 */

		$t .= '<h1>Taksit Ayarları</h1>
		<div class="row">';
		$default_rate = EticInstallment::getDefaultRate();
		$gwas = EticGateway::getGateways(true);

		if ($gwas) {

			$t .= '
		<div class="spp-box">
			<div>
		  <p class="spp-box-title">Min Taksit Tutarı</p>
		</div>
		 <div class="spp-box-option">
			<input name="spr_config[POSPRO_MIN_INST_AMOUNT]" value="' . (float) Eticconfig::get('POSPRO_MIN_INST_AMOUNT') . '" 
			class="spp-input" size="4" value="0" type="text">
		</div>
		<h2 class="spp-box-desc">Taksit seçeneğinin aylık tutarı en az bu kadar olmalıdır.</h2>
		<button class="spp-btn spp-blue-btn">Açıklama</button>
		</div>';


			$t .= '<div class="spp-box">
			  <p class="spp-box-title">Varsayılan POS</p>';
			$t .= '<div class="spp-box-option"><select name="spr_config_default_gateway">';
			foreach ($gwas as $gw)
				$t .= '<option value="' . $gw->name . '" ' . ($default_rate['gateway'] == $gw->name ? ' selected ' : '') . '>' . $gw->full_name . '</option>';
			$t .= '</select>'
				. '
				<h2 class="spp-box-desc">Taksit yapılamayan kartlar için bu POS\'u kullan.</h2>
				<button class="spp-btn spp-blue-btn">Açıklama</button>
				</div></div>';
		


			$t .= '<div class="spp-box">
				<div>
			  <p class="spp-box-title">Tek Çekim Komisyonu</p>
			</div>
		<input name="spr_config_default_rate" class="spp-input" size="4" value="' . (float) $default_rate['rate'] . '" type="number" step="0.01"/>
		<h2 class="spp-box-desc">Varsayılan POS kullanıldığında müşteriye yansıyacak yüzde</h2>
		<button class="spp-btn spp-blue-btn">Açıklama</button>
		</div>
		
		
		
		';

			$t .= '
			
			<div class="spp-box">
			  <p class="spp-box-title">Tek Çekim Maliyeti</p>
			 <div class="spp-box-option">
				<input name="spr_config_default_fee" size="4" class="spp-input" value="' . (float) $default_rate['fee'] . '" type="number" step="0.1">
			</div>
			<h2 class="spp-box-desc">Varsayılan POS kullanıldığı zaman sizden kesilecek yüzde</h2>
			<button class="spp-btn spp-blue-btn">Açıklama</button>
			</div>';
		} else {
			$t .= '<div class="spp-notifications">
			<svg class="svg-inline--fa fa-exclamation fa-w-6 spp-notifications-icon" aria-hidden="true" data-prefix="fas" data-icon="exclamation" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512" data-fa-i2svg=""><path fill="currentColor" d="M176 432c0 44.112-35.888 80-80 80s-80-35.888-80-80 35.888-80 80-80 80 35.888 80 80zM25.26 25.199l13.6 272C39.499 309.972 50.041 320 62.83 320h66.34c12.789 0 23.331-10.028 23.97-22.801l13.6-272C167.425 11.49 156.496 0 142.77 0H49.23C35.504 0 24.575 11.49 25.26 25.199z"></path></svg><!-- <i class="fas fa-exclamation spp-notifications-icon"></i> -->
			<div style="padding-left: 80px;">
			<h3>Uyarı</h3>
			<p>Kurulu POS hizetiniz bulunamadı. Lütfen önce bir POS kurulumunu yapınız.</p>
		  </div>
		  </div>';
		}

		$t .= '</div>';



		$t .= '<h1>Görünüm Ayarları</h1>
		<div class="row">';

// taksitleri göster
		$t .= '
		<div class="spp-box">
		  <p class="spp-box-title">Taksitler Sekmesi</p>
		 <div class="spp-box-option">
			<select name="spr_config[POSPRO_TAKSIT_GOSTER]">
				<option value="on"> Göster </option>
				<option value="off" ' . (EticConfig::get("POSPRO_TAKSIT_GOSTER") == 'off' ? 'SELECTED ' : '') . ' > Gizle </option>
			</select>
		</div>
		<h2 class="spp-box-desc">Ürün sayfasının altında bulunan taksit seçenekleri.</h2>
		<button class="spp-btn spp-blue-btn">Açıklama</button>
		</div>';

// PDF göster
		/* $t .= '
		  <div class="col-md-3 text-center sppbox bggray">
		  <h2>PDF Yerleşimi</h2>
		  <select name="spr_config[POSPRO_HOOK_PDF]" class="form-control">
		  <option value="Goster"> Göster </option>
		  <option value="Gizle" ' . (EticConfig::get("POSPRO_HOOK_PDF") == 'off' ? 'SELECTED ' : '') . ' > Gizle </option>
		  </select>
		  <br/>
		  PDF faturaya kredi kartı işlem bilgileri (silip) eklensin mi ?.<br/>
		  <a href="https://sanalpospro.com/sikca-sorulan-sorular/" class="btn btn-info btn-large" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
		  </div>';
		 * 
		 */

// ödeme tema
		$t .= '
		<div class="spp-box">
		  <p class="spp-box-title">Ödeme Ekranı Teması</p>
		 <div class="spp-box-option">
			<select name="spr_config[POSPRO_PAYMENT_PAGE]">';
			foreach (EticConfig::$order_themes as $k => $v):
				$t .= '<option value="' . $k . '" ' . (EticConfig::get("POSPRO_PAYMENT_PAGE") == $k ? 'SELECTED ' : '') . '>' . $v . '</option>';
			endforeach;
			$t .= '</select>
		</div>
		<h2 class="spp-box-desc">Ödeme sayfanızın yapısını seçiniz.</h2>
		<button class="spp-btn spp-blue-btn">Açıklama</button>
		</div>';




// taksit tema
		$t .= '
		
		<div class="spp-box">
		  <p class="spp-box-title">Taksitler Tema</p>
		 <div class="spp-box-option">
		<select name="spr_config[POSPRO_PRODUCT_TMP]">';
		foreach (EticConfig::$installment_themes as $k => $v):
			$t .= '<option value="' . $k . '" ' . (EticConfig::get("POSPRO_PRODUCT_TMP") == $k ? 'SELECTED ' : '') . '>' . $v . '</option>';
		endforeach;
		$t .= '</select>
		</div>
		<h2 class="spp-box-desc">Taksit seçenekleri sekmesinin görünümü.</h2>
		<button class="spp-btn spp-blue-btn">Açıklama</button>
		</div>
		</div>';
		
		$t .= '<div class="spp-notifications">
			<i class="fas fa-exclamation spp-notifications-icon"></i>
			<div style="padding-left: 80px;">
			<h3>Uyarı</h3>
			<p>SanalPOS PRO! tüm para birimlerini destekler. Fakat yabancı kurlarda ödeme alabilmeniz için hizmet aldığınız POS altyapısının da desteklemesi gerekir.</p>
		  </div>
		  </div>';


		$t .= '<input name="conf-form" type="hidden" value="1" />
		</div>
		' . wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . ' 
		</form>';
		return $t;
	}

	public static function getMasterPassForm()
	{
		$t = '
			<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js" integrity="sha384-SlE991lGASHoBfWbelyBPLsUlwY1GwNDJo3jSJO04KZ33K2bwfV9YBauFfnzvynJ" crossorigin="anonymous"></script>

			<form action="" method="post" id="masterpass_settings_form" action="#masterpass">
			<div>
				<div class="row ">
					<div class="col-md-8"> <!-- required for floating -->
						<p class="alert alert-info"><b>Masterpass</b> MasterCard tarafından sağlanan bir e-cüzdan sistemidir.
						<b>Masterpass</b> cüzdan hesabı olan kullanıcılara tek tıkla ödeme seçeneği gösterir.
						Masterpass hesabı olmayan müşterileriniz için ise hiç bir şey değişmez. Eskisi gibi kart bilgilerini girerek ödeme yaparlar.
						<b>Masterpass</b> mağazanızın ödeme sayfasını değiştirmeden ek bir seçenek olarak 
						çalışır. Ödemeleriniz eskisi gibi anlaşmalı olduğunuz bankanın posundan çekilmeye devam eder.
						<b>Herhangi bir komisyon ücreti almaz ve ödemenize dokunmaz.</b> 
						
						</p>
					</div>
					<div class="col-md-4"> 
						<a class="btn btn-default pull-right" href="https://sanalpospro.com/masterpass-entegrasyon/" target="_blank" ><i class="process-icon-help"></i> Yardım</a> 
						<button type="submit" name="submitspr" class="btn btn-default pull-right"><i class="process-icon-save"></i> Ayarları Kaydet</button>
					</div>
				</div>	
				<div class="row">
					<h2>Masterpass Üye İşyeri Olmanız İçin 8 Neden</h2>

				</div>
			<div class="row">
					<div class="col-sm-3 sppbox spph250">
						<h2><i class="fab fa-cc-mastercard mpicon" ></i><br/><br/>  BY MASTERCARD</h2>
						<p>Masterpass altyapısı tamamen MasterCard tarafından 
						sağlanan, dünyanın ve ülkemizin büyük markaları ve 
						alışveriş platformlarında kullanılan bir servistir.</p>
					</div>
					<div class="col-sm-3 sppbox spph250">
						<h2><i class="fas fa-sync mpicon" ></i><br/><br/> POS DEĞİŞİKLİĞİ YOK !</h2>
						<p>Mevcut kullandığınız pos sistemini veya bankanızı 
						değiştirmenize gerek yok. Masterpass altyapısı sadece 
						bir cüzdan servisidir. Ödemeleriniz eskisi gibi kendi 
						sanalposunuzdan tahsil edilir.</p>
					</div>
					<div class="col-sm-3 sppbox spph250">
						<h2><i class="far fa-money-bill-alt mpicon" ></i><br/><br/> 1 YIL ÜCRETSİZ DENEYİN</h2>
						<p>Masterpass e-cüzdan sistemini SanalPOS PRO! 
						sponsorluğu ile birlikte 1 yıl hiç bir ücret ödemeden 
						kullanabilirsiniz. Masterpass servisinde 
						hiç bir komisyon, ek ücret yoktur.</p>
					</div>
					<div class="col-sm-3 sppbox spph250">
						<h2><i class="far fa-thumbs-up mpicon" ></i><br/><br/> ETICSOFT DESTEĞİ </h2>
						<p>Bu eklentiyi Masterpass işbirliği kapsamında biz geliştirdik.
						İhtiyaç duyduğunuz tüm teknik desteği <b>ÜCRETSİZ</b> olarak biz sağlıyoruz.
						</p>
					</div>
					<div class="col-sm-3 sppbox spph250 ">
						<h2><i class="fas fa-user-secret mpicon" ></i><br/><br/>  DAHA GÜVENLİ !</h2>
						<p>Masterpass tarafından sağlanan ek kullanıcı doğrulama, cep telefonu doğrulama ve 
						kart sahipliği doğrulama fonksiyonları sayesinde alışverişler daha güvenli olur.</p>
					</div>
					<div class="col-sm-3 sppbox spph250">
						<h2><i class="fas fa-fighter-jet mpicon"></i><br/><br/> DAHA HIZLI ÖDEME !</h2>
						<p>Masterpass kullanan müşterileriniz tek tıkla ödeme yapar. 
						Kart bilgilerinin bankaya iletilmesi siteniz üzerinden değil Masterpass üzerinden sağlanır.
						</p>
					</div>
					<div class="col-sm-3 sppbox spph250">
						<h2><i class="fas fa-magic mpicon" ></i><br/><br/> DAHA ŞIK !</h2>
						<p>Masterpass entegrasyonu yaptığınızda SanalPOS PRO! ödeme sayfanıza 
						oldukça sade ve şık bir UI/UX tasarımı belirir. Bu standartlar 
						Masterpass tarafından belirlenmektedir.</p>
					</div>
					<div class="col-sm-3 sppbox spph250">
						<h2><i class="fas fa-power-off mpicon" ></i><br/><br/> TEK TIKLA AÇ/KAPAT !</h2>
						<p>Masterpass entegrasyonunu bu sayfadan istediğiniz an kapatabilir, entegrasyonu by-pass ederek pasifleştirebilirsiniz. 
						Kapattığınız an ödeme sayfanız tamamen eskisi gibi çalışır.
						</p>
					</div>
				</div>
				<div class="row">
					<p class="alert alert-info"> EticSoft R&D lab olarak, MasterCard ile Masterpass projesinde 
					işbiriliği ve sponsorluk konularında bir anlaşma imzaladık. 
					Bu kapsamda Opensource (açık kaynak) e-ticaret platformlarındaki 
					Masterpass standartlarına uygun eklentilerin geliştirmelerini,
					eklentilerin dağıtımı, tanıtımı ve teknik desteğini ücretsiz olarak biz sağlıyoruz. 
					Kullandığınız bu eklentinin tüm teknik sürecin arkasında biz olduğumuz gibi 
					Masterpass sürecinizde de sizin yanınızda olacağız.				
					</p>
				</div>

				<hr/>
				<div class="row ">
					<div class="col-md-4 text-center sppbox ' . (EticConfig::get("MASTERPASS_ACTIVE") != 'on' ? 'alert-danger-spp ' : 'alert-success-spp') . '">
						<h2>Durum</h2>
						<select name="spr_config[MASTERPASS_ACTIVE]">
							<option value="on"> Aktif </option>
							<option value="off" ' . (EticConfig::get("MASTERPASS_ACTIVE") != 'on' ? 'SELECTED ' : '') . '> Pasif </option>
						</select>
						<br/>
						MasterPass sistemini aktif eder.<br/>
						<a href="https://sanalpospro.com/sikca-sorulan-sorular/" class="btn btn-info btn-large" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
					</div>';
		$t .= '
					<div class="col-md-4 text-center sppbox bggray">
						<h2>MasterPass Client ID</h2>
						<input name="spr_config[MASTERPASS_CLIENT_ID]" class="form-control" size="4" value="' . (float) Eticconfig::get('MASTERPASS_CLIENT_ID') . '" type="number"/><br />
						Masterpass tarafından sağlanan mağaza ID no<br/>
						<a href="https://sanalpospro.com/sikca-sorulan-sorular/" class="btn btn-info btn-large" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
					</div>';
		$t .= '
					<div class="col-md-4 text-center sppbox bggray">
						<h2>MasterPass Ortamı</h2>
						<select name="spr_config[MASTERPASS_STAGE]">
							<option value="TEST"> Test Ortamı </option>
							<option value="UAT" ' . (EticConfig::get("MASTERPASS_STAGE") == 'UAT' ? 'SELECTED ' : '') . '> UAT Ortam </option>
							<option value="PROD" ' . (EticConfig::get("MASTERPASS_STAGE") == 'PROD' ? 'SELECTED ' : '') . '> Canlı Ortam </option>
						</select><br/>
						MasterPass ortam seçimi. Test ortamında ödemeler gerçekten tahsil edilmez.<br/>
						<a href="https://sanalpospro.com/sikca-sorulan-sorular/" class="btn btn-info btn-large" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
					</div>';
		$t .= '</div>';
		/*
		  $t .= '
		  <div class="row">
		  <div class="col-md-3 panel">
		  <h2>Participant number</h2>
		  <input name="spr_config[MASTERPASS_PART_NO]" class="form-control"  value="' . Eticconfig::get('MASTERPASS_PART_NO') . '" /><br />
		  Masterpass tarafından sağlanan Program participant name <br/>
		  <a href="https://sanalpospro.com/sikca-sorulan-sorular/" class="btn btn-info btn-large" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
		  </div>
		  <div class="col-md-3 panel">
		  <h2>Participant name</h2>
		  <input name="spr_config[MASTERPASS_PART_NAME]" class="form-control" value="' . Eticconfig::get('MASTERPASS_PART_NAME') . '" /><br />
		  Masterpass tarafından sağlanan Program participant number <br/>
		  <a href="https://sanalpospro.com/sikca-sorulan-sorular/" class="btn btn-info btn-large" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
		  </div>
		  <div class="col-md-3 panel">
		  <h2>MasterPass sponsor No</h2>
		  <i>PS702973</i><br /><hr/>
		  Masterpass sponsorluk no <br/>
		  <a href="https://sanalpospro.com/sikca-sorulan-sorular/" class="btn btn-info btn-large" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
		  </div>
		  <div class="col-md-3 panel">
		  <h2>MasterPass sponsor Name</h2>
		  <i>Eticsoft Sponsor</i><br /><hr/>
		  Masterpass sponsor adı <br/>
		  <a href="https://sanalpospro.com/sikca-sorulan-sorular/" class="btn btn-info btn-large" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
		  </div>
		  </div>';
		 * 
		 */
		$t .= '		
				<div class="row">
					<div class="col-md-8"> 
						<i>
							Enc ' . EticConfig::get("MASTERPASS_ENC_KEY") . ' Mac ' . EticConfig::get("MASTERPASS_MAC_KEY") . ' Last' . date("Y-m-d H:i:s", (int) EticConfig::get('MASTERPASS_LAST_KEYGEN')) . '
						</i>
					</div>
					<div class="col-md-4"> 
						<a class="btn btn-default pull-right" href="https://sanalpospro.com/sikca-sorulan-sorular/" target="_blank" ><i class="process-icon-help"></i> Yardım</a> 
						<button type="submit" name="submitspr" class="btn btn-default pull-right"><i class="process-icon-save"></i> Ayarları Kaydet</button>
					</div>

				<div>';
		/*
		  $t .= '	<div class="row">
		  <div class="col-md-12">
		  <h2>Entegrasyon Adımları Nelerdir ?</h2>
		  <ol>
		  <li><a href="https://eticsoft.com/masterpass-sozlesmesi.pdf" target="_blank">Bu adresten</a>
		  Masterpass İşlem Yönlendirme Hizmeti Taahhütnamesini indirip inceleyin.
		  Başvuru için tüm sayfalarını imzalayın ve kaşe basın.</li>
		  <li> Onaylandığınız zaman size Masterpass üye iş yeri bilgilerinizi göndereceğiz.  </li>
		  <li> Bu sayfadaki forma bu bilgileri giriniz. Bizim ve Masterpass ekibinin bir test uzmanı entegrasyonu test edecek. </li>
		  <li> Hepsi bu kadar. Dilerseniz Masterpass üye işyeri ikonunu sitenizin altına ekleyebilirsiniz. Desteğe ihtiyacınız olduğunda biz yanınızda olacağız. </li>
		  </ol>
		  </div>
		  </div>
		  ';
		 */
		$t .= '</div><input name="conf-form" type="hidden" value="1" />'
			. wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . ' 
		</form>';
		return $t;
	}

	public static function getAdminGatewaySettingsForm($module_dir)
	{
		$gateways = EticGateway::$gateways;
		if (!EticGateway::getGateways()) {
			return '<div class="text-center"> <i style="font-size:60px" class="process-icon-cancel"></i><br/> '
				. '<h1> Henüz hiç bir Sanal POS hizmeti kurulmamış !</h1>'
				. '<p>SanalPOS hizmeti aldığınız banka veya ödeme kuruluşlarının hizmetlerini '
				. '<a role="tab" data-toggle="tab" href="#integration"> Ödeme Yöntemleri </a> sekmesinden kurabilirsiniz.</p>'
				. ''
				. '</div>';
		}
		$t = '<form action="#pos" method="post" id="bank_settings_form" class="sppform ">
			<div style="width: 95%;display: inline-table; padding:15px;"> <!-- required for floating -->
			<div style="width:30%;float: left;">
				<h2>Pos ayarları</h2>
				<p>Pos ayarlarını bu sekmeden yapabilirsiniz. Taksit ayarları için <a href="#cards" role="tab" data-toggle="tab">buraya</a> tıklayınız.</p>
			</div>
			<div style="width: 70%;display: inline-block; margin-top:35px;">
				<a style="margin:5px; float: right;" class="spp-btn spp-green-btn" href="#integration" data-toggle="tab"><i class="process-icon-plus"></i> Yeni Pos</a> 
				<a style="margin:5px; float: right; padding:0.5em 2em;" class="spp-btn spp-red-btn" href="#help" role="tab" data-toggle="tab"><i class="process-icon-help"></i> Yardım</a> 
				<button style="margin:5px; float: right;" type="submit" name="submit" class="spp-btn spp-blue-btn"><i class="process-icon-save"></i> Tümünü Kaydet</button>
				<input type="hidden" name="submitgwsetting" value="1"/>
			</div>
			</div>
			<div>
        <!-- <div class="row"> -->
            <div id="spp-pos-setting-brand"> <!-- required for floating -->
                <!-- Nav tabs -->
                    ';
		$satir = 0;
		foreach (EticGateway::getGateways(false) as $gwbutton):
			$t .= '<a class="spp-pos-setting-button" href="#bf_' . $gwbutton->name . '_form">
                                <img src="' . plugins_url() . '/sanalpospro/img/gateways/' . $gwbutton->name . '.png" width="125px"/>
                            </a>';
			$satir++;
		endforeach;

		$t .= '
            </div>

            <div class="col-md-10 ">
                <!-- Tab panes -->
                <div id="spp-pos-setting-content">';
		$satir = 0;
		foreach (EticGateway::getGateways(false) as $gwd):
			if ($gw = New EticGateway($gwd->name)):

				$gwe = EticGateway::$gateways->{$gw->name};
				if (!isset($gwe->families)) {
					continue;
				}


				if (!isset($gwe->paid) OR ! $gwe->paid) {
					Etictools::rwm($gwe->full_name . ' POS lisansınız aktif değil. Bu sanalPOS kullanılamaz.'
						. '<br/>Lütfen eticsoft ile iletişime geçiniz.');
				}
				//print_r(EticGateway::$gateways); exit;
				$t .= '<!-- BANKA -->

                        <div style="'.($satir == 0 ? "display:block;" : "display:none;").'" id="bf_' . $gw->name . '_form">
                            <div class="spp-pos-setting-info">
                                <input name="pdata[' . $gw->name . '][id_bank]" type="hidden" value="' . $gw->name . '" />
                                <h2>' . $gw->full_name . ' Pos Ayarları </h2>
                                <hr/>';
				if (isset($gw->params->test_mode) && $gw->params->test_mode == 'on'):
					$t .= '<div class="alert alert-danger-spp">' . $gw->full_name . ' test modunda çalışıyor. '
						. 'Test modunda yapılan siparişlerin başarılı görünür fakat ödemesi alınmaz. </div>';
				endif;
				$t .= '<h2>Parametreler</h2>' . $gw->createFrom();

				$t .= ' <br/>
						<div class="row">
                        <button type="submit" value="' . $gw->name . '" name="submit_for" class="spp-btn spp-blue-btn"><i class="icon icon-save"></i> Ayarları Kaydet</button>
						</div>

			<hr/>
			<br/>
			</div>
			<div style="width: 25%;display: inline-block;" class="col-md-5 sppbox bggray text-center">
			<img class="spp-pos-setting-button spp-pos-setting-img" src="' . plugins_url() . '/sanalpospro/img/gateways/' . $gw->name . '.png"/>';

				if (json_decode($gwe->families)):
					$t .= '<div style="line-height:25px"> <span class="spp-pos-setting-text">Taksit yapabildiği kartlar</span>';
					foreach (json_decode($gwe->families) as $family):
						$t .= '<img class="spp-img-thumbnail" width="40px" src="' . plugins_url() . '/sanalpospro/img/cards/' . $family . '.png"/>';
					endforeach;
					$t .= '</div>';
				endif;


				$t .= '<div id="' . $satir . '-kmp" >
                    
    			<table class="table">';

				$t .= '</table>
			</div>
			<a href="https://sanalpospro.com/sikca-sorulan-sorular/" style="width: 40%;text-align: center;text-decoration: none;" class="spp-btn spp-green-btn" target="_blank"><i class="icon-question-sign"></i> Açıklama </a>
            
 
            
            
			<div id="' . $gw->name . '_remove" class="spp-pos-setting-text-bg">
			<a data-toggle="collapse" style="color:#fff !important; font-weight:700;" class="spp-pos-setting-text" data-target="#' . $gw->name . '_remove">
				<i class="icon-remove"></i> Bu POS\'u Kaldır</a>
                <div>
                    <div style="color:#fff !important;" class="spp-pos-setting-text"> Dikkat! POS silme işlemi geri alınamaz. 
                    Sildiğiniz POS\'u daha sonra yeniden kurabilirsiniz. Fakat
                    ' . $gw->full_name . ' için girdiğiniz kullanıcı bilgileri ve oranlar da silinecektir.</div>
                    <div class="toggle"><button type="submit" class="spp-btn spp-red-btn" style="border: 1.5px solid white;box-shadow: 0 0 25px 5px rgba(255, 255, 255, 0.63);background: white;color: black; display:block;" name="remove_pos" value="' . $gw->name . '">Kaldır</button></div>
                </div>
            </div>
			</div>';

				if (EticTools::getValue('adv')):
					$t .= '<div class="col-sm-6">
					<input name="' . $gw->name . '[lib]" value="' . $gw->lib . '"/>
				</div>';
				endif;


				$t .= '</div>';
				$satir++;
			endif;
		endforeach;
		$t .= '
            
            </div>
            </div>

            <!-- </div> -->
            </div>
            <input name="bank-form" type="hidden" value="1" />
            ' . wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . ' 
		</form>';
		return $t;
	}

	public static function getAdminIntegrationForm()
	{

		$t = '
        <div>
            <div class="row">';
		$exists_gws = array();


		foreach (EticGateway::getGateways() as $gateway)
			$exists_gws [] = $gateway->name;



		$t .= '
            <div style="padding:10px;" class="col-md-12">
            <h2>Kullanmak İstediğiniz POS servisini seçiniz</h2>
            EticSoft sadece BDDK lisanslı ve <b>güvenilir</b> ödeme kuruluşları ve bankalar ile entegrasyon sağlıyor.
            Kullanmak istediğiniz ödeme sistemi aşağıda yoksa, ilgili ödeme şirketi/banka standartlara 
            uygun bulunmamış veya bizimle hiç iletişime geçmemiş olabilir. 
			<hr/>
            <p align="center" class="alert alert-info">
            Tüm bankaları ve ödeme sistemlerini birlikte çalışacak şekilde (hibrit) kullanabilirsiniz.
            Örnek: Tüm kartların tek çekimlerini Xbankası üzerinden, 5 taksitli ödemeleri Ypay ödeme 
            kuruluşu üzerinden, ABC kartının 2 taksitli ödemelerini Zpara ödeme kuruluşu üzerinden tahsil edebilirsiniz.						
            Kart türlerine ödeme yönetiminin nasıl çalışacağını seçmek için 
            <a href="#cards" role="tab" data-toggle="tab">Taksitler</a> tıklayınız.
            </p>
			</div>
                    ';
			$t .= '<div style="width:100%;margin-top:30px;" class="spp-metod-container">';
		foreach (EticGateway::$gateways as $k => $gw) {
		
			$gw->is_bank = isset($gw->is_bank) && $gw->is_bank ? true : false;
			$gw->lib = isset($gw->lib) && $gw->lib ? $gw->lib : $k;
			$gw->eticsoft = isset($gw->eticsoft) && $gw->eticsoft ? true : false;

			
			$t .= '<div style="background:linear-gradient(-180deg, #03A9F4, #3F51B5); border-radius:2px; margin:13.4px 20px 75px 10px; box-shadow: 0 0px 10px 0 rgba(0, 0, 0, 0.3);" class="panel-body spph250">';
			$t .= '<img src="' . plugins_url() . '/sanalpospro/img/gateways/' . $k . '.png" class="spp-metod-img"/>';

			if (!$gw->active):
				$t .= '<p align="center" style="padding:77.1px 10px;" class="spp-label-card-info">Bu entegrasyon geçici olarak aktif değil</p>';
			else :
				if ($gw->eticsoft)
					$t .=''; //'<p align="center" class="spp-label-card-info"> EticSoft ' . ($k == 'onlineodemesistemi' ? ' tarafından geliştirilmiş arge ürünü yazılımdır' : 'resmi iş ortağıdır.') . '</p>';
				else
				if (!$gw->is_bank)
					$t .= '<p align="center" class="spp-label-card-info"> EticSoft iş ortağı DEĞİLDİR. Teknik destekte kısıtlamalar olabilir. </p>';
			endif;
			if (json_decode($gw->families) && $gw->active):
				$t .= '<div style="line-height:25px"> <span style="display: block; text-align: center; color: #fff; font-size: 12px;
					margin: 5px;">Taksit yapabildiği kartlar:</span><div class="spp-inst-cards" style="line-height:25px">';
				foreach (json_decode($gw->families) as $family):
					$t .= '<span class="spp-label-info">' . ucfirst($family) .'</span>';
				endforeach;
				$t .= '</div></div>';
			endif;
			if ($gw->active):

				if (in_array($k, $exists_gws)):
					$t .= '<p align="center" class="spp-label-card-info"> Kurulu !</p>';
				else:
					$t .= '<p class="spp_price">' . ($gw->price == 0 ? 'Ücretsiz' : number_format($gw->price, 2) . ' TL/yıllık') . '</p>';
					if (isset($gw->paid) AND $gw->paid):
						$t .= '<div class="spp-label-card-help"><form style="padding:1.3px" action="" method="post">'
							. '<input type="hidden" name="add_new_pos" value="' . $k . '"/>'
							. '<button type="submit" style="margin: 39.4px auto;" class="spp-btn spp-green-btn"><i class="fa fa-play"></i> Kurulum </button>';
						$t .= wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . '</form></div>';
					else:
						$t .= EticConfig::getApiForm(array('redirect' => '?controller=gateway&action=buy&gateway=' . $k));
					endif;
				endif;

			endif;
			$t .= '</div>';
		}
		$t .= '
            </div></div>
            </div>';

		return $t;
	}

	public static function getApiForm($custom_array = false, $button_content = ' Demo Başlat')
	{
		$api = New SanalPosApiClient(1);
		$apilogininfo = $api->getLoginFormValues();
		$t = '<div class="spp-label-card-help">
		<form action="' . $apilogininfo['url'] . '" target="_blank" method="post">';
		foreach ($apilogininfo as $k => $v)
			$t .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
		if ($custom_array AND is_array($custom_array))
			foreach ($custom_array as $k => $v)
				$t .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
		$t .= '<input type="hidden" name="api_login" value="1">'
			. '<button onClick="javascript:window.open(`https://sanalpospro.com/wordpress/' . $k . '-sanal-pos-kurulumu/`, `_blank`);" style="margin-top: 15px;margin-bottom: 15px;" target="_blank" class="spp-btn spp-red-btn"><i class="fas fa-life-ring"></i> Yardım</button>'
			. '<button type="submit" style="margin-top: 15px;margin-bottom: 15px;" class="spp-btn spp-blue-btn"><i class="fa fa-power-off"></i>' . $button_content . '</button>'
			. wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . ' </form></div>';
		return $t;
	}

	public static function getAdminToolsForm()
	{

		$t = '<form action="#tools" method="post" id="toolsform">

            <div>
            <div class="spp-tools-page">';
		$t .= '
		<div style="border-left:5px solid #eb3636" class="spp-notifications">
        <i style="background:#eb3636" class="fas fa-eraser spp-notifications-icon"></i>
        <div style="padding-left: 80px;">
        <h3>Eski Kayıtları Temizle</h3>
        <p>SanalPOS PRO! üzerinden yapılan alışveriş işlemlerinin tüm detaylarını, banka sorgu ve cevaplarını veri tabanına kayıt eder.
            (Kredi kartı bilgileri kayıt edilmez.) Bu bilgileri banka kayıtlarındaki uyumsuzluklarla karşılaştırmak, 
            hata ayıklamak ve olası hukuki ihtilaflarda resmi mercilere sunmak için sizin sisteminize kayıt ediyoruz. 
            Veritabanınızda çok fazla veri biriktiğinde bu bilgileri zaman zaman temizleyebilirsiniz.
            Bu temizleme işlemi son bir ay işlemleri hariç tüm işlemlerin detaylarını geri getirilemeyecek şekilde siler.</p>
            <button href="#" style="
            width: 25%;
            margin-left: 35%;
            border: 1.5px solid #eb3636;
            margin-right: 35%;
            box-shadow: 0 0 15px -4px #eb3636;
            background: #eb3636;" class="spp-btn spp-blue-btn" name="clear-logs" value="1">Eski Logları Temizle</button>
      </div>
      </div>';
		$t .= '
		<div style="border-left:5px solid #ae00ef; height: auto;" class="spp-notifications">
        <i style="background:#ae00ef" class="fas fa-server spp-notifications-icon"></i>
        <div style="padding-left: 80px;">
        <h3>Sunucu Uyumluluk Testi</h3>
        <p>Pos ve ödeme kuruluşlarının sistemleri bazı özel gereksinimlere ihtiyaç duyar. 
            Bu gereksinimleri ve sisteminizin uyumluluğunu kontrol etmek için aşağıdaki aracı kullanabilirsiniz. 
            Bu araç ayrıca SanalPOS PRO! modülünün çalışmasına engel olacak/etkileyecek modülleri/eklentileri de listeler.</p>
            <button href="#" style="
            width: 25%;
            margin-left: 35%;
            border: 1.5px solid #ae00ef;
            margin-right: 35%;
            box-shadow: 0 0 15px -4px #ae00ef;
            background: #ae00ef;" class="spp-btn spp-blue-btn" name="check-server" value="1">Sunucuyu ve Sistemi Kontrol Et</button>
      </div>
      </div>';
		$t .= '
		<div style="border-left:5px solid #fcb90f; height: auto;" class="spp-notifications">
        <i style="background:#fcb90f" class="fas fa-wrench spp-notifications-icon"></i>
        <div style="padding-left: 80px;">
        <h3>Eski Versiyon Ayarları</h3>
        <p>Daha önce bir SanalPOS PRO! versiyonu kullandıysanız daha önce girilen banka parametrelerini göstermek için
             aşağıdaki butona tıklayabilirsiniz. Bu araç daha önceki versiyonlarda kurulu bankaları ve bilgilerini listeler.</p>
             <button href="#" style="
             width: 25%;
             margin-left: 35%;
             border: 1.5px solid #fcb90f;
             margin-right: 35%;
             box-shadow: 0 0 15px -4px #fcb90f;
             background: #fcb90f;" class="spp-btn spp-blue-btn" name="check-oldtables" value="1">Eski Bankaları Göster</button>
      </div>
      </div>';
		$t .= '</div>'; // Row
		/*
		  $cats = New HelperTreeCategoriesCore(1);
		  $cats->setUseCheckBox(true);
		  $cats->setTitle('Taksit uygulanmayacak kategoriler');
		  $cats->setInputName('spr_config_res_cats');

		  if (is_array(EticConfig::getResCats()))
		  $cats->setSelectedCategories(EticConfig::getResCats());
		  $t .= '<div class="row">';

		  $t .= '
		  <div class="col-md-6 panel">
		  <h2>Taksit Kısıtlaması</h2>
		  <p> Taksit yapılmayacak ürünlerin kategorilerini seçiniz.
		  Alışveriş sepetinde bu kategorilerden ürünler varsa taksitli alışveriş yapılmayacak,
		  ödemeler tek çekim olarak yapılabilecektir. Taksit kısıtlaması olan ürünler
		  sepete atıldığında müşteriye bir uyarı mesajı gösterilmektedir.
		  <b>Taksit kısıtlaması olan ürünleriniz yoksa hiç bir kategoriyi seçmeyiniz !</b>
		  </p>
		  ' . $cats->render() . '
		  <button type="submit" name="savetoolsform" value="1" class="btn btn-default pull-right"><i class="process-icon-save"></i> Kısıtlamaları Kaydet</button>
		  </div>';

		  $t .= '<div class="col-md-6 bgblue sppbox">'
		  . '<h2>FP007 Dolandırıcılık Koruma Sistemi</h2>'
		  . '<div class="alert alert-info">SanalPOS PRO! mağazalarının alışveriş süreçlerini güvenli hale getiren'
		  . 'FP007 proje kodlu yazılım servisimiz henüz yapım aşamasında !</div>'
		  . '<hr>'
		  . '</div>';
		  $t .= '</div>'; // Row

		 */
		$t .= '</div>'; // Panel



		$t .= wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . '</form>  ';
		return $t;
	}
	public static function getCampaigns(){
		$t = '
		<style>
			@media only screen and (max-width: 600px) {
				.spp_bootstrap-wrapper-egrid .row {
					display: grid;
					grid-column-gap: 15px;
					grid-row-gap: 10px;
					grid-template-columns: auto;
				}
			}
			@media only screen and (min-width: 600px) {
				.spp_bootstrap-wrapper-egrid .row {
					display: grid;
					grid-gap: 10px;
					grid-template-columns: auto auto auto;
				}
			}
		</style>
		<div class="panel spp_bootstrap-wrapper-egrid">
		<div class="row">
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/2VCy0Gi">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/ipara/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/38j47QA">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/paybyme/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/2CXqsY9">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/paynet/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/2YShNij">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/paytr/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/38maylP">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/paytrek/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		<div class="col-md-4">
		<a target="_blank" href="https://bit.ly/3ijNQ2x">
		<img style="width:100%;" src="https://sanalpospro.com/img/kampanyalar/parampos/kampanya.png"
		class="thumbnail center-block" />
		</a>
		</div>
		</div>
		</div>';
		return $t;
	}

	public static function getApiSettingsForm()
	{

		$t = '<form action="#tools" method="post" id="toolsform">

            <div>
            <div class="row">
            <div class="col-md-12 sppbox bgred"> <!--required for floating -->
            <h2>Eski Kayıtları Temizle</h2>
            <p>SanalPOS PRO! üzerinden yapılan alışveriş işlemlerinin tüm detaylarını, banka sorgu ve cevaplarını veri tabanına kayıt eder. (Kredi kartı bilgileri kayıt edilmez.)
            Bu bilgileri banka kayıtlarındaki uyumsuzluklarla karşılaştırmak, hata ayıklamak ve olası hukuki ihtilaflarda resmi mercilere sunmak için sizin sisteminize kayıt ediyoruz.
            Veritabanınızda çok fazla veri biriktiğinde bu bilgileri zaman zaman temizleyebilirsiniz. Bu temizleme işlemi son bir ay işlemleri hariç tüm işlemlerin detaylarını <b>geri getirilemeyecek şekilde</b> siler.</p>
            <hr/>
            <button class="btn btn-large btn-warning" name="clear-logs" value="1">Eski logları temizle</button>
            </div>
            <div class="col-md-6 text-center sppbox bgpurple">
            <h2>Sunucu Uyumluluk Testi</h2>
            <p>
            Pos ve ödeme kuruluşlarının sistemleri bazı özel gereksinimlere ihtiyaç duyar. Bu gereksinimleri ve sisteminizin uyumluluğunu kontrol etmek için aşağıdaki aracı kullanabilirsiniz.
            Bu araç ayrıca SanalPOS PRO!modülünün çalışmasına engel olacak/etkileyecek modülleri/eklentileri de listeler.</p>
            <hr/>
            <button class="btn btn-large btn-warning" name="check-server" value="1">Sunucuyu ve sistemi kontrol et</button>
            <br/>
            <br/>
            </div>
            </div>
            </div>
			' . wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . '
            </form> 
        ';
		return $t;
	}

	public static function getCardSettingsForm($module_dir)
	{
		$all_gws = EticGateway::getGateways(true);
		$def_rate = EticInstallment::getDefaultRate();

		$t = '<form action="#cards" id="cards" method="post"> 
                <div class="panel-3-grid-container">
				<div style="padding:15px;">
                    <div class="col-md-4"> <!-- required for floating -->
                        <h2>Kartlar ve taksit seçenekleri</h2>
                        <p>Taksit ayarlarını bu sekmeden yapabilirsiniz. Pos ayarları için <a href="#pos" role="tab" data-toggle="tab">buraya</a> tıklayınız.</p>
                    </div>
                    <div style="margin:10px;" class="col-md-8"> 
                        <button class="spp-btn spp-blue-btn" type="submit" name="submitcards" class="btn btn-default pull-right"><i class="process-icon-save"></i> Oranları Kaydet</button>
                        <input type="hidden" name="submitcardrates" value="1"/>
					</div>';
					$t .= '<div id="spp-bonus">';
						foreach (EticConfig::$families as $family):
					   	$t .= '<a href="#'. $family .'"><img src="' . plugins_url() . '/sanalpospro/img/cards/' . $family . '.png" class="spp-pos-setting-button"/></a>';
						endforeach;
					$t .='</div>
                </div>';



		$t .= '<div style="margin-top:25px;" id="spp-pos-inst-content">';
		foreach (EticConfig::$families as $key=>$family):
			$gwas = EticGateway::getByFamily($family, true);

			$t .= '<div id="'.$family.'" class="spp-inst-table" style="max-height: 900px; text-align:center; '.($key == 0 ? "display:block;" : "display:none;").'">'
				. '<img src="' . plugins_url() . '/sanalpospro/img/cards/' . $family . '.png" style="
				display: block;
				margin-left: auto;
				margin-right: auto;
				border-radius: 5px;
				background: #e2e2e2;" /><br/>';
			if (!$gwas OR empty($gwas)) {
				$t .= '<h2>Uygun POS Yok veya Kurulmamış</h2>'
					. '<p><i style="font-size: 45px;" class="icon-remove"></i></p>' . ucfirst($family) . ' kart ailesine taksit yapabileceğiniz'
					. ' hiç bir POS sistemi kurulu değil. ' . ucfirst($family) . ' ödemelerini taksitsiz olarak alabilirsiniz.<br/>'
					. '<div class="alert alert-info">Sadece Tek Çekim Ödeme alabilirsiniz. Tek Çekimler için '
					. 'tanımlanmış POS sistemi: ' . ucfirst($def_rate['gateway']) . ' </div>';
				$t .= '<div style="line-height:25px"> <h2>Taksit yapabilen pos sistemleri</h2>';
				foreach (EticGateway::getByFamily($family, false) as $gwall)
					$t .= ' <span class="label label-info">' . ucfirst(EticGateway::$gateways->{$gwall}->full_name) . '</span>';
				$t .= '</div></div>';
				continue;
			}

			$t .= '<div class="row"><div id="spp-inst-table-header">'
				. 'Tümü için toplu seçim </div>'
				. '<div class="col-sm-6 col-xs-6">'
				. '<select class="inst_select_all" id="' . $family . '" name="' . $family . '_all">'
				. '<option value="">Seçiniz</option>'
				. '<option value="0">Taksit Yok</option>';

			foreach ($gwas as $gwa)
				$t .= '<option value="' . $gwa . '">' . ucfirst(EticGateway::$gateways->{$gwa}->full_name) . '</option>';

			$t .= '</select></div></div>'
				. '<table class="table">'
				. '<tr>'
				. '<td>Taksit</td>'
				. '<td>Pos</td>'
				. '<td>Oran (%)<br/><small>Müşteriye<br/> yansıyacak</small></td>'
				. '<td>Maliyet (%)<br/><small>Sizden <br/> kesilecek</small></td>'
				. '</tr>';
			for ($i = 1; $i <= 12; $i++) :
				$ins = EticInstallment::getByFamily($family, $i);

				$t .= '<tr>'
					. '<td>' . $i . '</td>'
					. '<td><select class="inst_select ' . $family . ' form-control" id="row_' . $family . '_' . $i . '" name="' . $family . '[' . $i . '][gateway]">'
					. '<option value="0">Kapalı</option>';

				if ($i == 1) {
					$t .= '<option value="' . $def_rate['gateway'] . '">' . ucfirst($def_rate['gateway']) . ''
						. '(Varsayılan)</option>';
					foreach ($all_gws as $gwa)
						$t .= '<option ' . ($ins && $ins['gateway'] == $gwa->name ? 'selected ' : '')
							. 'value="' . $gwa->name . '">' . ucfirst($gwa->name) . '</option>';
				} else
					foreach ($gwas as $gwa)
						$t .= '<option ' . ($ins && $ins['gateway'] == $gwa ? 'selected ' : '')
							. 'value="' . $gwa . '">' . ucfirst(EticGateway::$gateways->{$gwa}->name) . '</option>';

				$t .= '</select></td>'
					. '<td><div class="input-group">' . ($ins ? '<span class="row_' . $family . '_' . $i . ' input-group-addon">%</span>' : '' )
					. '<input class="form-control row_' . $family . '_' . $i . '  input_' . $family . '" size="5" step="0.01" type="number" '
					. 'name="' . $family . '[' . $i . '][rate]" value="' . ($ins ? (float) $ins['rate'] : '') . '">'
					. '</div></td>'
					. '<td><div class="input-group' . ($ins['fee'] == 0 ? ' has-error' : '') . '">'
					. ($ins ? '<span class="row_' . $family . '_' . $i . ' input-group-addon">%</span>' : '' )
					. '<input type="number" step="0.01" class="form-control row_' . $family . '_' . $i . ' input_' . $family . '"'
					. ' name="' . $family . '[' . $i . '][fee]" value="' . ($ins ? (float) $ins['fee'] : '') . '">'
					. '</div></td>'
					. '</tr>';
			endfor;

			$t .= '</table>';
			$t .= '<div style="line-height:25px; text-align:center;"><span style="display:block;">' . ucfirst($family) . ' kartlarına taksit yapabilen pos sistemleri</span>';
			foreach (EticGateway::getByFamily($family, false) as $gwall)
				$t .= ' <span class="spp-label-' . (in_array($gwall, $gwas) ? 'success-spp' : 'default') . '">'
					. '' . ucfirst(EticGateway::$gateways->{$gwall}->name) . '</span>';
			$t .= '</div>';
			$t .= '</div>';
		endforeach;
		$t .= '<div class="clear clearfix"></div>
            </div></div>
			' . wp_nonce_field('woocommerce-settings', '_wpnonce', true, false) . '
            </form>';
		return $t;
	}

	public static function getHelpForm()
	{

		$t = '
			<div class="spp-support-grid-container">
				<!-- <div style="clear: unset;" class="spp-support-help">            
					<h1>Yardıma mı ihtiyacınız var? <br/> Hemen eticsoft\'u çağırın !</h1>
						<img src="' . plugins_url() . '/sanalpospro/img/help2.png" style="height: 300px;" class="img-responsive text-center" id="payment-logoh" />
				</div> -->
				<div class="spp-support-help">
					<h1><label style="color:red; font-weight:900;">Yardıma mı İhtiyacınız Var <i class="fas fa-headset"></i></label></h1><hr/>
					' . EticConfig::getApiForm(array('redirect' => '?controller=home&action=addticket'), ' Destek Sistemine Bağlan')
			. '<hr/>
					<a href="https://sanalpospro.com/wordpress/" target="_blank" class="spp-btn spp-green-btn"><i class="fa fa-book" aria-hidden="true"></i> Kullanım Klavuzu</a> <hr/>
					<a href="mailto:destek@eticsoft.com?Subject=Wordpress SanalPOS PRO " 
					   class="spp-btn spp-green-btn"><i class="fa fa-envelope" aria-hidden="true"></i> destek@eticsoft.com</a>
					<hr/>
					<a class="spp-btn spp-green-btn"><i class="fa fa-phone-square" aria-hidden="true"></i> 0242 241 59 85</a> 
					<hr />
				</div>
						<div style="margin:20px;">            
							<a target="_blank" href="https://iyonhost.com/wordpress-uyumlu-hosting-php//">
								<img src="' . plugins_url() . '/sanalpospro/img/hosting.png" style="border-top: 4px solid #135891;border-radius: 5px;margin-bottom: 10px;box-shadow: 0 0 20px 3px rgba(19, 88, 145, 0.1);" class="img-responsive" id="payment-logo" />
							</a>          
							<a target="_blank" href="https://eticsoft.com/shop/tr/13-woocommerce">
								<img src="' . plugins_url() . '/sanalpospro/img/shop.png" style="border-top: 5px solid #9b5c8f;border-radius:5px;box-shadow: 0 0 20px 3px rgba(155, 92, 143, 0.2);" class="img-responsive" id="payment-logox" />
							</a>
						</div>
						<div class="spp-support-social-media">
						<label class="spp-btn"><i class="fab fa-facebook-f"></i></label>
						<label class="spp-btn"><i class="fab fa-twitter"></i></label>
						<label class="spp-btn"><i class="fab fa-linkedin-in"></i></label>
						<label class="spp-btn"><i class="fab fa-instagram"></i></label>
						<label class="spp-btn"><i class="fab fa-github"></i></label>
						<label class="spp-btn"><i class="fab fa-youtube"></i></label>
						<div style="border-top: 4px solid #f3c715;box-shadow: 0 0 20px 3px rgba(243, 199, 21, 0.2);" class="spp-support-help">            
							<h2>Projenizi bizimle geliştirmek ister misiniz ?</h2>
							<a target="_blank" href="https://eticsoft.com/">
								<img style="width: 100%;" src="' . plugins_url() . '/sanalpospro/img/eticsoft-infogram.png" class="img-responsive" id="payment-logoy" />
							</a>
						</div>
						</div>
			</div>
		';
		return $t;
	}

	public static function saveCardSettingsForm()
	{
		foreach (EticConfig::$families as $family) {

			if (!Etictools::getValue($family) OR ! is_array(Etictools::getValue($family)))
				continue;
			$installments = Etictools::getValue($family);
			foreach ($installments as $i => $ins) {
				if ($ins['gateway'] == '0') {
					EticInstallment::deletebyFamily($family, $i);
					continue;
				}
				$ins['divisor'] = $i;
				$ins['family'] = $family;
				EticInstallment::save($ins);
			}
		}
		Etictools::rwm('Taksitler Güncellendi !', true, 'success-spp');
	}

	public static function saveToolsForm()
	{
		if (Etictools::getValue('check-oldtables')) {
			if (EticSql::tableExists('spr_bank')) {
				$old_banks = EticSql::getRows('spr_bank');
				if ($old_banks) {
					$old_txt = '';
					foreach ($old_banks as $old_bank) {
						$params = unserialize($old_bank['params']);
						$old_txt .= '<hr/><b>' . $old_bank['ad'] . '</b> Parametreler <br/>';
						foreach ($params['params'] as $k => $v)
							$old_txt .= $k . ' : ' . $v['value'] . '</br>';
					}
					Etictools::rwm('Eski versiyona ait bankalar ' . $old_txt, true, 'success-spp');
				}
			}
			Etictools::rwm('Eski versiyona ait bilgi bulunamadı', true, 'warning');
		} else {
			if (!Etictools::getValue('spr_config_res_cats') OR ! is_array(Etictools::getValue('spr_config_res_cats')))
				Eticconfig::set('SPR_RES_CATS', 'off');
			else
				Eticconfig::set('SPR_RES_CATS', json_encode(Etictools::getValue('spr_config_res_cats')));
			Etictools::rwm('Taksit Kısıtlamaları Güncellendi !', true, 'success-spp');
		}
	}

	public static function saveGatewaySettings()
	{
		if (Etictools::getValue('remove_pos')) {
			$gw = New EticGateway(Etictools::getValue('remove_pos'));
			$gw->delete();
		}

		foreach (EticGateway::getGateways() as $gw) {
			if (Etictools::getValue('submit_for') AND Etictools::getValue('submit_for') != $gw->name)
				continue;
			$data = Etictools::getValue($gw->name);
			if (isset($data['lib']))
				$gw->lib = $data['lib'];
			$lib = EticGateway::$api_libs->{$gw->lib};
			if (!$lib OR ! $data) {
				Etictools::rwm($gw->name . ' Güncelleme hatası tespit edildi. Lütfen formu gözden geçiriniz ' . $gw->lib, true, 'fail');
				continue;
			}
			foreach ($lib->params as $pk => $pv)
				if (isset($data['params'][$pk]))
					$gw->params->{$pk} = $data['params'][$pk];
				$gw->test_mode = isset($data['test_mode']) ? $data['test_mode'] : false;
				if ($gw->name == "paybyme") {  
					if (Etictools::getValue('submit_for')) 
						Eticconfig::paybymeInstallment($data["params"]["username"],$data["params"]["token"],$data["params"]["keywordID"]);

				} 
				if ($gw->save()) {
					
					Etictools::rwm($gw->full_name . ' güncellendi', true, 'success');
				}
			}
		}

		public static function paybymeInstallment($username,$password,$keywordId)
		{
			$installment_url = "https://pos.payby.me/webServicesExt/FunctionInstallmentList"; 
			$assetPrice = "10000";
			$currencyCode = "TRY";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $installment_url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$username&password=$password&keywordId=$keywordId&assetPrice=$assetPrice&currencyCode=$currencyCode");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);
			$result = json_decode($server_output);
			$ins = array(); 

			foreach ($result->InstallmentList as $key => $il) {
				$ins['gateway'] = "paybyme";
				$ins['rate'] = ($il->lastPriceRatio*100)-100;
				$ins["fee"] = $il->commissionShare;
				$ins['divisor'] = $il->installmentCount;
				$ins['family'] = strtolower($il->program) == "bankkart" ? "combo" : strtolower($il->program) == "miles&smiles" ? "miles-smiles" : strtolower($il->program);
				EticInstallment::save($ins);
				
			}
		}

	public static function saveGeneralSettings()
	{
		//Change stage needs refresh token
		if (isset(EticTools::getValue('spr_config')['MASTERPASS_STAGE']))
			if (EticConfig::get('MASTERPASS_STAGE') != EticTools::getValue('spr_config')['MASTERPASS_STAGE'])
				EticConfig::set('MASTERPASS_LAST_KEYGEN', 0);
		if ($spr_config = EticTools::getValue('spr_config'))
			foreach ($spr_config as $k => $v)
				Eticconfig::set($k, $v);

		EticSql::updateRow('spr_installment', array(
			'rate' => Etictools::getValue('spr_config_default_rate'),
			'fee' => Etictools::getValue('spr_config_default_fee'),
			'gateway' => Etictools::getValue('spr_config_default_gateway')
			), array('family' => 'all'));
	}

	public static function getConfigNotifications()
	{
		//Check
		foreach (EticGateway::getGateways(true) as $gw) {
			if (isset($gw->params->test_mode) && $gw->params->test_mode == 'on')
				Etictools::rwm($gw->full_name . ' <strong>Test Modunda Çalışıyor</strong>');
		}
		if (EticSql::getRow('spr_installment', 'fee', 0)) {
			Etictools::rwm('Taksit tablosundaki maliyetler (Sizden kesilecek oranlar) eksik girilmiş.'
				. '<br/>Bu durum hesaplamada hatalara neden olabilir.', true, 'danger');
		}
	}

	public static function cleardebuglogs()
	{
		return EticSql::deleteRows('spr_debug');
	}

	public static function testSys()
	{
		return true;
	}

	public static function getResCats()
	{
		return json_decode(EticConfig::get('SPR_RES_CATS'));
	}

	public static function displayError($body, $title = 'Hata !')
	{
		return '<div class="spp-notifications">
		<svg class="svg-inline--fa fa-exclamation fa-w-6 spp-notifications-icon" aria-hidden="true" data-prefix="fas" data-icon="exclamation" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512" data-fa-i2svg=""><path fill="currentColor" d="M176 432c0 44.112-35.888 80-80 80s-80-35.888-80-80 35.888-80 80-80 80 35.888 80 80zM25.26 25.199l13.6 272C39.499 309.972 50.041 320 62.83 320h66.34c12.789 0 23.331-10.028 23.97-22.801l13.6-272C167.425 11.49 156.496 0 142.77 0H49.23C35.504 0 24.575 11.49 25.26 25.199z"></path></svg><!-- <i class="fas fa-exclamation spp-notifications-icon"></i> -->
		<div style="padding-left: 80px;">
		<h3>' . $title . '</h3>
		<p>' . $body . '</p>
	  </div>
	  </div>';
	}
}
