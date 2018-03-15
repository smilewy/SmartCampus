<template>
  <div class="g-evaluationGroup g-container">
    <header class="g-evaluationGroupHeader">
      <div class="g-liOneRow">
        <div class="g-flexStartRow">
          <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
            <img src="../../../../assets/img/commonImg/icon_return.png"/>
            返回流程图
          </el-button>
          <h2 class="selfCenter g-headerH">设置考评分组</h2>
        </div>
        <el-button @click="saveAjaxClick" type="primary" class="defineHeight">保存</el-button>
      </div>
      <el-button @click="addGroupClick" class="g-imgContainer blueButton">
        <img class="" src="../../../../assets/img/commonImg/icon_add_01.png"/>
        新建被评分组
      </el-button>
    </header>
    <section class="g-sectionMargin">
      <div class="g-eg_part">
        <div class="g-eg_part_left">
          <el-form ref="egGroupForm" :model="egGroupForm" :rules="groupRules" label-width="138px" label-position="left">
            <el-row class="hasBorderBottom" v-for="(row,rowIndex) in egGroupForm.formData" :key="rowIndex">
              <div class="g-liOneRow_space">
                <div class="g-eg-item">
                  <el-form-item label="被考评人分组名称:"  prop="name">
                    <el-input placeholder="请输入分组名称" class="g-eg-groupName" v-model="row.name"></el-input>
                  </el-form-item>
                  <el-form-item class="g-hasMoreInput"  label="各组评委权重:">
                    <div class="g-eg-column" v-for="(col,colIndex) in row.judgeWeight">
                      <span v-text="col.name"></span>
                      <el-input v-model="col.value" class="defineInputWidth"></el-input>
                      <span>%</span>
                    </div>
                  </el-form-item>
                </div>
                <div class="g-eg-img">
                  <span class="el-icon-close" @click="deleteClick(rowIndex)" :data-msg="row.id"><!--delete删除--></span>
                </div>
              </div>
            </el-row>
          </el-form>
        </div>
        <div class="g-eg_part_right"></div>
      </div>
    </section>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import {
    judgeGroupLoad,//点击新增
    judgeGroupSave,//得到数据与保存接口
  } from '@/api/http'
  export default{
    data(){
      return {
        /*form表单单条数据，添加直接为formData中添加该数据字段*/
        formOneObject: {},
        /*form表单*/
        egGroupForm: {
          /*ajax*/
          formData: [],
        },
        /*rule*/
        groupRules: {
          /*name:[{required:'true'}]*/
        },
        /*send ajax param*/
        _id: '',
        _group: [],//转换过百分比的参数
      }
    },
    methods: {
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name: 'evaluationManagement'});
      },
      /*点击新建被评分组*/
      addGroupClick(){
        req.ajaxSend('/school/TeacherEvaluate/getJudgeGroup', 'post', {id: this._id}, (res) => {
          let data =
            {
              id: '',
              name: '',
              judgeWeight: []
            };
          res.forEach(val => {
            let judgeWeight = {
              id: val.id,
              name: val.name,
              value: ''
            };
            data.judgeWeight.push(judgeWeight);
          });
          this.egGroupForm.formData.push(data);
        });
      },
      /*处理数据，转为百分比*/
      handlerDataPercent(data){
        data.forEach((row, rowI) => {
          row.judgeWeight.forEach((col, colI) => {
            col.value = Number(col.value) * 100;
          });
        });
      },
      /*处理数据，转为小数*/
      handlerDataPoint(data){
        data.forEach((row, rowI) => {
          row.judgeWeight.forEach((col, colI) => {
            col.value = Number(col.value) / 100;
          });
        });
      },
      /*send ajax*/
      /*保存*/
      saveAjaxClick(){
        let _isFoo = true;//必填项是否填写
        for (let i = 0; i < this.egGroupForm.formData.length; i++) {
          /*判断名称*/
          if (!this.egGroupForm.formData[i].name) {
            this.vmMsgWarning( '请输入被考评人分组名称！' );
            _isFoo = false;
            break;
          }
          else {
            /*判断权重*/
            let _total = 0;//判断各组权重为多少
            for (let j = 0; j < this.egGroupForm.formData[i].judgeWeight.length; j++) {
              _total += Number(this.egGroupForm.formData[i].judgeWeight[j].value);
            }
            if (_total != 100) {
              this.vmMsgWarning( '各组权重和必须等于100！' );
              _isFoo = false;
            }
          }
        }
        if (_isFoo) {
          /*发送请求*/
          this._group = JSON.parse(JSON.stringify(this.egGroupForm.formData));
          judgeGroupSave({id: this._id, type: 'save', group: this._group}).then(data => {
            if (data.status) {
              this.vmMsgSuccess( '保存成功！' );
            }
            else {
              this.vmMsgError( '保存失败！' );
            }
          });
        }
      },
      /*删除*/
      deleteClick(idx){
        this.$confirm('是否删除数据？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then((value) => {
          this.egGroupForm.formData.splice(idx, 1);
        }).catch((value) => {
        });
      },
      /*得到初始数据*/
      getLoadAjax(){
        judgeGroupSave({id: this._id}).then(data => {
          if (data.status) {
            this.egGroupForm.formData = data.data;
            this.handlerDataPercent(this.egGroupForm.formData);
          } 
          /*提出单条数据，得到新建被评分组数据*/
          if (this.egGroupForm.formData.length > 0) {
            for (let key in this.egGroupForm.formData[0]) {
              if (this.egGroupForm.formData[0][key] instanceof Array) {
                /*设置该键的值为数组*/
                this.$set(this.formOneObject, key, []);
                this.egGroupForm.formData[0][key].forEach((value, index) => {
                  /*给数组插入对象值*/
                  this.formOneObject[key].push(JSON.parse(JSON.stringify(value)));
                  for (let childKey in this.formOneObject[key][index]) {
                    /*清空数组对象value值*/
                    if (childKey == 'name' || childKey == 'id') {
                      /*保留评委分组名字和id*/
                      continue;
                    }
                    else {
                      this.formOneObject[key][index][childKey] = 0;
                    }
                  }
                });
              }
              else {
                this.$set(this.formOneObject, key, '');
              }
            }
          }
        });
      },
    },
    created(){
      this._id = this.$route.params.id;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';

  .g-eg-column {
    span {
      .fontSize(14);
      color: @normalColor;
    }
  }

  button.blueButton {
    margin-top: 20/16rem;
  }
</style>


