<template>
  <div class="g-timeSetting">
    <header class="g-timeHeader">
      <el-button class="g-gobackChart RedButton" @click="goBackChart">
        <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png"/>
        返回流程图
      </el-button>
      <el-button class="blueButton" @click="saveSetting">保存</el-button>
    </header>
    <section class="g-timeSection">
      <div class="g-timeContent_one">
        <header class="g-contentOne_header">周表设置</header>
        <section class="g-contentOne_section">
          <el-table class="g-timeSettingTable" :data="weekChoose" style="width:100%">
            <el-table-column label="时段/周" prop="rowName"></el-table-column>
            <el-table-column v-for="n in 7" :key="n" :label="weekData[n-1]">
              <template slot-scope="props">
                <el-checkbox v-if="props.row.rowName=='上午'" :disabled="ifStartUp || section.morningCount==0"
                             v-model="weekAjaxData[props.$index][n-1]"></el-checkbox>
                <el-checkbox v-if="props.row.rowName=='下午'" :disabled="ifStartUp || section.noon==0"
                             v-model="weekAjaxData[props.$index][n-1]"></el-checkbox>
                <el-checkbox v-if="props.row.rowName=='晚上'" :disabled="ifStartUp || section.night==0"
                             v-model="weekAjaxData[props.$index][n-1]"></el-checkbox>
              </template>
            </el-table-column>
          </el-table>
          <p class="prompt">* 勾选之后即表示上课，并且只有在“节次设置”中设置了当前时段的节次，周表设置才会保存成功。</p>
        </section>
      </div>
      <div class="g-timeContent_two">
        <header class="g-contentTwo_header">节次设置</header>
        <section class="g-contentTwo_section">
          <div class="g-courseChoose clear_fix">
            <div class="courseChoose-row">
              <span class="g-rowTimer">上午</span>
              <input type="text" v-model="section.morningCount"/>
              <span class="g-RowCourse">节</span>
            </div>
            <div class="courseChoose-row">
              <span class="g-rowTimer">下午</span>
              <input type="text" v-model="section.noon"/>
              <span>节</span>
            </div>
            <div class="courseChoose-row">
              <span class="g-rowTimer">晚上</span>
              <input type="text" v-model="section.night"/>
              <span>节</span>
            </div>
            <el-button :disabled="ifStartUp" class="createTimer" type="primary" @click="createTimeTable">生成时间表
            </el-button>
          </div>
          <el-table class="g-timeSettingTable"
                    v-loading="loading"
                    element-loading-text="拼命加载中"
                    element-loading-spinner="el-icon-loading"
                    :data="courseSetting"
                    style="width:100%">
            <el-table-column label="节次/时间">
              <template slot-scope="props">
                <div v-text="'第'+(props.$index+1)+'节'"></div>
              </template>
            </el-table-column>
            <el-table-column label="开始时间">
              <template slot-scope="props">
                <el-time-picker
                    :editable="false"
                    :disabled="ifStartUp || (props.$index != 0 && !props.row.startTime )"
                    :ref="'pickerStart' + props.$index"
                    placeholder="开始时间"
                    @focus="timeLimitSetStart(props.$index)"
                    @change="timeChange(props.$index, 'start', props.row.startTime)"
                    v-model="props.row.startTime"
                    :picker-options.sync="pickerOptionsStart">
                </el-time-picker>
              </template>
            </el-table-column>
            <el-table-column label="结束时间">
              <template slot-scope="props">
                <el-time-picker
                    :editable="false"
                    :disabled="ifStartUp || !props.row.endTime"
                    :ref="'pickerEnd' + props.$index"
                    placeholder="结束时间"
                    @focus="timeLimitSetEnd(props.$index)"
                    @change="timeChange(props.$index, 'end', props.row.endTime)"
                    v-model="props.row.endTime"
                    :picker-options.sync="pickerOptionsEnd">
                </el-time-picker>
              </template>
            </el-table-column>
          </el-table>
        </section>
      </div>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {courseSetting, courseSettingLoad} from '@/api/http'
  import {handlerAjaxData} from '@/assets/js/common'
  import moment from 'moment'
  export default{
    data(){
      const _self = this;
      return {
        pkListId: '',
        /*是否发布*/
        ifStartUp: false,
        /*星期课程时间选择*/
        weekChoose: [
          {rowName: '上午'},
          {rowName: '下午'},
          {rowName: '晚上'},
        ],
        /*星期课程时间选择——星期数据双向绑定*/
        /*发送请求时将其转化为Number型*/
        weekAjaxData: [
          [false, false, false, false, false, false, false],
          [false, false, false, false, false, false, false],
          [false, false, false, false, false, false, false],
        ],
        /*节次设置*/
        /*单行数据*/
        oneRowData: {
          startTime: '',
          endTime: ''
        },
        /*节次设置table框*/
        courseSetting: [],
        /*节数填写表单*/
        section: {
          morningCount: '',
          noon: '',
          night: '',
        },
        /*星期转换*/
        weekData: ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'],
        pickerOptionsStart: {
          selectableRange: '00:00:00 - 23:59:59'
        },
        pickerOptionsEnd: {
          selectableRange: '00:00:00 - 23:59:59'
        },
        loading: false,
        flagPicker: ''
      }
    },
    methods: {
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name: 'examinationChart'});
      },

      timeChange( index, startOrEnd, time){
        // 如果改变的是上课开始时间
        if( startOrEnd == 'start' ){
          let lesson = this.courseSetting[index];
          // 如果开始时间小于已存在结束时间，则结束时间不改变
          if( !lesson.endTime || lesson.endTime < time ){
            // 设置本节课结束时间
            lesson.endTime = time;
          }
        } else { // 如果改变的是上课结束时间
          let tempIndex = index + 1;
          let les = this.courseSetting[tempIndex];
          // 如果不是最后一节课，设置下节课开始时间
          if( tempIndex <= this.courseSetting.length - 1 && ( !les.startTime || les.startTime < time )){
            les.startTime = time;
          }
        }
      },

      timeLimitSetStart(index){
          this.flagPicker = 'pickerStart' + index;
        // 如果设置的是第一节课的开始时间，则不用任何限制
        if( index == 0 ) { return; }

        let prevLessonEndTime = this.courseSetting[index - 1].endTime;

        if( !prevLessonEndTime ){
          this.vmMsgWarning('请先设置上一节课的结束时间！'); return;
        }
        this.pickerOptionsStart.selectableRange = this.timeHandler(prevLessonEndTime) + ' - 23:59:59';
      },
      timeLimitSetEnd(index){
        this.flagPicker = 'pickerEnd' + index;

        let startTime = this.courseSetting[index].startTime;

        if( !startTime ){
          this.vmMsgWarning('请先设置本节课的开始时间！'); return;
        }
        this.pickerOptionsEnd.selectableRange = this.timeHandler(startTime) + ' - 23:59:59';
      },
      /*时间处理器*/
      timeHandler(date){
        return moment(date).format('HH:mm:ss');
      },
      /*保存设置*/
      saveSetting(){
        /*isSettingWeek为判定周表设置是否设置*/
        let isSettingWeek = this.weekAjaxData.some((value) => {
          return value.some((childValue) => {
            return childValue;
          });
        });
        /*isSettingTime为时间判定设置完没*/
        let isSettingTime = this.courseSetting.every((value) => {
          return value.startTime && value.endTime;
        });

        if (isSettingWeek && this.courseSetting.length > 0 && isSettingTime) {
          this.saveAjaxSetting();
        } else {
          this.vmMsgWarning('请完成周表设置，节次设置，节次时间设置所有设置！');
        }
      },
      /*生成时间表点击事件*/
      createTimeTable(){
        /*总上课节数*/

        // 是否已经存在排课数据
        let everyEmpty = (obj) => {
          return !obj.startTime && !obj.endTime;
        }

        // 排课函数
        let scheduleClass = () => {
          this.courseSetting = [];
          const courseNum = Number(this.section.morningCount) + Number(this.section.noon) + Number(this.section.night);
          for (let i = 0; i < courseNum; i++) {
            /*向节次设置table表中加时间设置表*/
            this.courseSetting.push(JSON.parse(JSON.stringify(this.oneRowData)));
          }
        }
        // 检验是否设置节次
        if( !this.section.morningCount && !this.section.noon && !this.section.night){
          this.vmMsgWarning('请设置课程节次!'); return false;
        }
        if( !this.courseSetting.every( everyEmpty ) ){
          this.$confirm('排课数据已存在，是否重新排课?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then( scheduleClass );
        }else{
          scheduleClass();
        }
      },
      /*ajax=====*/
      saveAjaxSetting(){
        /*转换复选框的值为0或1*/
        const week = [[], [], []], len = this.courseSetting.length;
        this.weekAjaxData.forEach((value, index) => {
          value.forEach((childValue, childIndex) => {
            week[index].push(Number(childValue));
          });
        });
        for (let [key, obj] of this.courseSetting.entries()) {
          if (key + 1 != len) {
            // 判断某节课的开始时间是否大于上节课的结束时间
            if (new Date(obj.endTime).getTime() >= new Date(this.courseSetting[key + 1].startTime).getTime()) {
              this.vmMsgWarning('第' + (key + 2) + '节课的开始时间必须大于第' + (key + 1) + '节课的结束时间!'); return false;
            }

            // 判断某节课的结束时间是否大于该节课的开始时间
            if(new Date(obj.endTime).getTime() <= new Date(obj.startTime).getTime()){
              this.vmMsgWarning('第' + (key + 1) + '节课的结束时间必须大于第' + (key + 1) + '节课的开始时间!'); return false;
            }

          }
        }
        courseSetting({
          pkListId: this.pkListId,
          data: {week: week, section: this.section, day: this.courseSetting}
        }).then(data => {
          if (data.statu) {
            this.vmMsgSuccess('保存成功！');
          } else {
            this.vmMsgError('保存失败！');
          }
        });
      },
      getLoadAjax(){
        this.loading = true;
        courseSettingLoad({pkListId: this.pkListId}).then(data => {
          this.loading = false;
          if (data.statu) {
            if (data.data) {
              // 后台传过来data.data.day里面的时间是字符串，须转换成日期格式
              this.courseSetting = data.data.day.map( o => {
                return {
                  endTime: new Date(o.endTime),
                  startTime: new Date(o.startTime)
                }
              });
              this.section = data.data.section;
              this.weekAjaxData = data.data.week;
              this.ifStartUp = data.ifStartUp;
            }
          } else {
            this.vmMsgError('请求失败，请重试!');
          }
        });
      },

      bindScrollEvent(){
        if(this.flagPicker){
            this.$refs[this.flagPicker].$data.pickerVisible = false;
        }
      },

      getScrollEventTarget(){
          let domContaner = document.getElementsByClassName('common_RContainer');
          if( domContaner.length <= 0 ) { return new Element(); }
          return domContaner[0];
      }
    },
    created(){
      this.pkListId = sessionStorage.pkListId;
      this.getLoadAjax();

      /** 用于处理el-time-picker组件在显示的时候滚动屏幕出现定位为fixed的情况
       *  由于无法设置el-time-picker随着滚动，因此换种方式设置其在屏幕滚动时隐藏
       */
      this.getScrollEventTarget().addEventListener('scroll', this.bindScrollEvent );
    },
    beforeDestroy(){
        // 解除绑定，避免在其他页面还存在该事件函数
      this.getScrollEventTarget().removeEventListener('scroll', this.bindScrollEvent );
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
  @import '../../../../style/arrangeClasses/timeSetting.less';
</style>




