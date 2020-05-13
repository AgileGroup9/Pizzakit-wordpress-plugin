import React from 'react';
import './style.scss';

class App extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			childComponent: props.defaultChild || App.defaultState.childComponent,
			childProps: props.defaultChildProps || App.defaultState.childProps
		};

		/** @type {React.RefObject<HTMLElement>} */
		this.ref = React.createRef();
	}

	/**
	 * Changes what this "App" displays as it's child.
	 * Children displayed from this "App" will get a `navigateTo` function in
	 * addition to the provided `props` that they can use to navigate
	 * elsewhere.
	 * @param {*} reactComponent A string (for a built in type) or a
	 *                           function/class for a React element.
	 * @param {*} [props]        Some properties for a React element.
	 */
	navigateTo(reactComponent, props) {
		this.setState({
			childComponent: reactComponent,
			childProps: props
		});
	}

	render() {
		return (
			<div ref={this.ref}>
				{React.createElement(this.state.childComponent, { ...this.state.childProps, navigateTo: this.navigateTo.bind(this) })}
			</div>
		);
	}

	componentDidUpdate() {
		this.ref.current.scrollIntoView();
	}
}

App.defaultState = {
	childComponent: () => <p>Pizzakit: Order form (something went wrong 😢)</p>,
	childProps: { }
}

export default App;