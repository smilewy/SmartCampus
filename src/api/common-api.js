

exports.install = function (Vue, options) {

    const vm = new Vue();

    /**
     * 操作确认提示框
     * 参数传入的时候使用对象的方式
     * @param { msg }  提示信息
     * @param { confirmBtnText } 确认按钮文字
     * @param { cancelBtnText } 取消按钮文字
     * @param { confirmCallback } 点击确认按钮后的回调函数
     * @param { cancelCallback } 点击取消按钮后的回调函数
     */
    Vue.prototype.vmConfirm = function ( { 
        msg = '确认进行该操作吗？', 
        confirmBtnText = '确定', 
        cancelBtnText = '取消' , 
        confirmCallback = () => {},
        cancelCallback = () => {} } = {} 
      ) {
        let showConfirmBtn = typeof confirmCallback == 'function';
        vm.$confirm( msg, '提示', {
            showConfirmButton: showConfirmBtn,
            confirmButtonText: confirmBtnText,
            cancelButtonText: cancelBtnText,
            type: 'warning',
        }).then( data => {
          confirmCallback( data );
        }).catch( ( e ) => {
            if( e != 'cancel' ) {
                this.vmMsgError( e.message || e );
            }
            cancelCallback && cancelCallback();
        });
    };

    /**
     * 操作成功提示框
     * @param { msg } 提示信息
     */
    Vue.prototype.vmMsgSuccess = function ( msg = '操作成功！' ) {
        vm.$message({
            showClose: true,
            message: msg,
            type: 'success'
        });
    };

    /**
     * 操作告警提示框
     * @param { msg } 提示信息
     */
    Vue.prototype.vmMsgWarning = function ( msg = '操作过程中出了问题！' ) {
        vm.$message({
            showClose: true,
            message: msg,
            type: 'warning'
        });
    };

    /**
     * 操作错误提示框
     * @param { msg } 提示信息
     */
    Vue.prototype.vmMsgError = function ( msg = '操作出错！' ) {
        vm.$message({
            showClose: true,
            message: msg,
            type: 'error'
        });
    };

    /**
     * 操作等待函数，全屏loading
     */
    Vue.prototype.vmLoadingFull = function( msg = '处理中，请稍后...' ) {
        return vm.$loading({
            lock: true,
            text: msg,
            background: 'rgba(234, 234, 234, 0.86)'
        });
    };

    Vue.prototype.vmPySegSort = function( arr, empty ){
        
    }
};