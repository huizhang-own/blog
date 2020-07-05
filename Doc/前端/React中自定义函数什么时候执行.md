#React中自定义函数什么时候执行

```
<div id="root"></div>

<script type="text/babel">
  class Toggle extends React.Component {
    constructor(props) {
      super(props);
      this.state = {
        isToggleOn: true,
        count: 0
      };

      // This binding is necessary to make `this` work in the callback
      this.handleClick = this.handleClick.bind(this);
    }

    handleClick() {
      console.log("第" + this.state.count + "次");
      this.setState(prevState => ({
        isToggleOn: !prevState.isToggleOn,
        count: this.state.count + 1
      }));
    }

    render() {
      return (
        
<div>
  点击才会执行
  <button onClick={() => this.handleClick()}>
    {this.state.isToggleOn ? 'ON' : 'OFF'}
  </button>
  会自动执行
  <button onClick={this.handleClick()}>
    {this.state.isToggleOn ? 'ON' : 'OFF'}
  </button>
  点击执行，可以用于传参数
  <button onClick={this.handleClick}>
    {this.state.isToggleOn ? 'ON' : 'OFF'}
  </button>
</div>
); } } ReactDOM.render( <Toggle/>, document.getElementById('root') );</script>
```

> 可以自己测试三个按钮，推荐第三种写法，既可以避免立即执行且可以传参


### 转载地址
[https://blog.csdn.net/MoYongShi/article/details/80435401](https://blog.csdn.net/MoYongShi/article/details/80435401)
