import React from 'react';
import OrderForm from "./OrderForm";

class Policy extends React.Component {
	goBack() {
		this.props.navigateTo(OrderForm, { initState: this.props.state, post_address: this.props.post_address });
	}
	
	render() {
		return(
			<p className="policy-page">
				<h2>Köpvillkor</h2>
					<a onClick={() => this.goBack()}>&lt; Tillbaka</a>
                    <p></p>
				    <p>PRISER OCH BETALNING</p><p>

                    Varje vara anges med pris inklusive moms. I kundvagnen kan man se det totala priset
                    inklusive alla avgifter, moms, frakt och betalning.</p><p>

                    ÅNGERRÄTT</p><p>

                    Om du enligt Konsumentköplagen (SFS 1990:932) mottagit en felaktig vara har du rätt
                    att reklamera den inom tre år på grund av felet. Reklamationen ska ske snarast
                    efter att felet upptäckts. Om varan är felaktig kommer du att krediteras
                    motsvarande belopp alternativt ersättas med en likvärdig vara om det inte innebär
                    en oskälig kostnad för restaurangen.</p><p>

                    Om du utnyttjar din ångerrätt är skyldig att hålla varan i lika gott skick som när
                    du fick den. Du får inte använda den, men naturligtvis försiktigt undersöka den. Om
                    varan skadas eller kommer bort på grund av att du är vårdslös förlorar du
                    ångerrätten.</p><p>

                    INTEGRITETSPOLICY</p><p>

                    När du lägger din beställning hos oss uppger du dina personuppgifter. I samband med
                    din registrering och beställning godkänner du att vi lagrar och använder dina
                    uppgifter i vår verksamhet för att fullfölja avtalet gentemot dig enligt denna
                    integritetspolicy. Du har enligt Dataskyddsförordningen rätt att få den information
                    som vi har registrerat om dig. Om den är felaktig, ofullständig eller irrelevant
                    kan du begära att informationen ska rättas eller tas bort. Kontakta oss i så fall
                    via e-post. Du har rätt att få utdrag av den information vi har sparad om dig och
                    att när som helt återkalla ditt samtycke till vårt lagrande av dina personuppgifter.
                    Du har rätt att ta upp eventuella klagomål gällande vår hantering av personuppgifter
                    till Datainspektionen.</p><p>

                    RETURER</p><p>

                    Returer sker på din egen bekostnad utom om varan är defekt eller om vi har packat
                    fel.</p><p>

                    EJ UTHÄMTADE VAROR</p><p>

                    Om du inte hämtat ut dina varor inom avtalat tidsintervall är du skyldig att
                    kontakta restaurangen. Restaurangen har rätt att debitera dig för hela ordern, men
                    ej uthämtade varor kan förvaras under skälig tid efter överenskommelse, i mån av
                    utrymme och tid.</p><p>

                    Se även konsumentverket och distansavtalslagen, samt EU:s gemensamma
                    tvistlösningssida http://ec.europa.eu/odr.</p>
					<a onClick={() => this.goBack()}>&lt; Tillbaka</a>
			</p>
		);
	}
}

export default Policy;
